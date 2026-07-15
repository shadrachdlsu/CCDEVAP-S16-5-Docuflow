<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/connections.php";

/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../login.php");
    exit();

}

$userId = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| USER INFORMATION
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    u.user_id,
    u.full_name,
    u.email,
    o.office_name,
    r.role_name

FROM users u

LEFT JOIN offices o

ON u.office_id = o.office_id

INNER JOIN roles r

ON u.role_id = r.role_id

WHERE u.user_id = ?

LIMIT 1

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);

$user = $stmt->fetch();

/*
|--------------------------------------------------------------------------
| DASHBOARD COUNTS
|--------------------------------------------------------------------------
*/

/* Pending */

$sql = "

SELECT COUNT(*) total

FROM document_routes

WHERE signatory_user_id = ?

AND status='Waiting'

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);

$pending = $stmt->fetch()["total"];

/* Signed */

$sql = "

SELECT COUNT(*) total

FROM document_routes

WHERE signatory_user_id=?

AND status='Signed'

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);

$signed = $stmt->fetch()["total"];

/* Finished */

$sql = "

SELECT COUNT(*) total

FROM documents

WHERE creator_id=?

AND status='Completed'

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);

$finished = $stmt->fetch()["total"];

/* Requests */

$sql = "

SELECT COUNT(*) total

FROM document_requests

WHERE requested_by_id=?

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);

$requests = $stmt->fetch()["total"];

/*
|--------------------------------------------------------------------------
| CHART DATA
|--------------------------------------------------------------------------
*/

$chartData = [

    $pending,
    $signed,
    $finished,
    $requests

];

/*
|--------------------------------------------------------------------------
| PENDING DOCUMENTS
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    d.document_id,

    d.tracking_code,

    d.title,

    dt.type_name,

    o.office_name,

    dr.status,

    d.file_path

FROM document_routes dr

INNER JOIN documents d

ON dr.document_id = d.document_id

INNER JOIN document_types dt

ON d.type_id = dt.type_id

LEFT JOIN offices o

ON d.current_office_id = o.office_id

WHERE dr.signatory_user_id=?

AND dr.status='Waiting'

ORDER BY d.created_at DESC

";

$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);

$documents = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| PAPER TRAIL
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    dt.created_at,

    dt.action_taken,

    u.full_name,

    d.status

FROM document_trails dt

INNER JOIN users u

ON dt.action_by_user_id=u.user_id

INNER JOIN documents d

ON dt.document_id=d.document_id

ORDER BY dt.created_at DESC

LIMIT 20

";

$trail = $pdo->query($sql)->fetchAll();

/*
|--------------------------------------------------------------------------
| DOCUMENT TYPES
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

type_id,

type_name

FROM document_types

WHERE is_active=1

ORDER BY type_name

";

$types = $pdo->query($sql)->fetchAll();


/*
|--------------------------------------------------------------------------
| AJAX REQUEST HANDLER
|--------------------------------------------------------------------------
*/

if(isset($_GET["action"]))
{

    header("Content-Type: application/json");


    switch($_GET["action"])
    {

        case "documents":

            echo json_encode($documents);

            break;


        case "statistics":

            echo json_encode(
            [
                "pending"=>$pending,
                "signed"=>$signed,
                "finished"=>$finished,
                "requests"=>$requests
            ]);

            break;


        case "profile":

            echo json_encode($user);

            break;


        case "paperTrail":

            echo json_encode($trail);

            break;


        default:

            echo json_encode(
            [
                "success"=>false
            ]);

            break;

    }


    exit();

}

?>