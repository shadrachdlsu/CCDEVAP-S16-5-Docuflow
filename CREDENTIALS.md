# DocuFlow - Seed User Credentials

This document provides a reference list of pre-configured user credentials for testing and development across the Admin, Secretary, and Member modules of **DocuFlow**.

---

## Universal Password

All seed user accounts in the database use the default password:

```text
dlsu
```

---

## 1. System Administrators (Role ID: 1)

Administrators have full access to system-wide configuration, office management, user approvals, document type definitions, and system analytics.

| User ID | Full Name | Email Address | Assigned Office | Password | Status |
| :---: | :--- | :--- | :---: | :---: | :---: |
| `1` | System Admin | `admin@docuflow.local` | *None (Global)* | `dlsu` | Approved |
| `8` | System Administrator | `admin@office.gov` | *None (Global)* | `dlsu` | Approved |

---

## 2. Secretaries (Role ID: 2)

Secretaries manage document routing, approvals, incoming request queues, and status updates for their respective assigned offices.

| User ID | Full Name | Email Address | Assigned Office | Password | Status |
| :---: | :--- | :--- | :--- | :---: | :---: |
| `2` | Registrar Secretary | `registrar.secretary@docuflow.local` | Registrar Office | `dlsu` | Approved |
| `3` | Finance Secretary | `finance.secretary@docuflow.local` | Finance Office | `dlsu` | Approved |
| `4` | Dean Secretary | `secretary@docuflow.local` | Dean Office | `dlsu` | Approved |
| `5` | IT Secretary | `it.secretary@docuflow.local` | IT Office | `dlsu` | Approved |
| `9` | HR Secretary | `hr.secretary@docuflow.local` | Human Resources Office | `dlsu` | Approved |
| `19` | Admissions Secretary | `admissions.secretary@docuflow.local` | Admissions Office | `dlsu` | Approved |
| `20` | R&D Secretary | `rnd.secretary@docuflow.local` | Research & Development Office | `dlsu` | Approved |

---

## 3. Members (Role ID: 3)

Members create document requests, track submission progress, upload attachments, and download approved document outputs.

### Approved Accounts
| User ID | Full Name | Email Address | Assigned Office | Password | Status |
| :---: | :--- | :--- | :--- | :---: | :---: |
| `6` | Juan Member | `juan.member@docuflow.local` | Registrar Office | `dlsu` | Approved |
| `7` | Maria Signatory | `member@docuflow.local` | Finance Office | `dlsu` | Approved |
| `10` | Sample Member | `member2@office.gov` | Dean Office | `dlsu` | Approved |
| `11` | Alex Santos | `alex.santos@docuflow.local` | Registrar Office | `dlsu` | Approved |
| `12` | Keith Rodriguez | `keith@docuflow.local` | IT Office | `dlsu` | Approved |
| `13` | Elena Gomez | `elena.gomez@docuflow.local` | Human Resources Office | `dlsu` | Approved |
| `14` | David Chen | `david.chen@docuflow.local` | Finance Office | `dlsu` | Approved |
| `15` | Patricia Cruz | `patricia.cruz@docuflow.local` | Dean Office | `dlsu` | Approved |
| `16` | Robert Taylor | `robert.taylor@docuflow.local` | Admissions Office | `dlsu` | Approved |
| `17` | Sophia Martinez | `sophia.martinez@docuflow.local` | Research & Development Office | `dlsu` | Approved |
| `18` | Liam Wilson | `liam.wilson@docuflow.local` | IT Office | `dlsu` | Approved |

### Pending & Rejected Accounts (Testing Edge Cases)
| User ID | Full Name | Email Address | Assigned Office | Password | Status |
| :---: | :--- | :--- | :--- | :---: | :---: |
| `21` | Shad Paje | `shad@paje.me` | Registrar Office | `dlsu` | Pending |
| `22` | Carlos Reyes | `carlos.reyes@docuflow.local` | Finance Office | `dlsu` | Pending |
| `23` | Angela Diaz | `angela.diaz@docuflow.local` | Human Resources Office | `dlsu` | Pending |
| `24` | Marcus Vance | `marcus.vance@docuflow.local` | Dean Office | `dlsu` | Rejected |
| `25` | Jessica Alba | `jessica.alba@docuflow.local` | IT Office | `dlsu` | Rejected |
