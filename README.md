# ERP for Nextcloud — Employee and Human Resources Management Module

**Developed by:** Luis Ángel
**Version:** Beta 2025
**License:** MIT

---

## 📄 Overview

This ERP module for Nextcloud enables companies to fully manage employee information, departments, positions, teams, and benefits, natively integrated into the Nextcloud ecosystem.

Designed for SMEs, firms, and organizations seeking digital sovereignty and customization while avoiding high commercial SaaS costs.

---

## 🏢 Current Features

### 🔹 Human Capital

* **Employees**

  * General, banking, and job data.
  * Vacation history.
  * Savings fund.
  * Work structure (Partner / Manager / Employee).
  * Assignment to departments and positions.
  * Internal notes.
  * Documents (records, memos, identifications).
* **Departments**

  * Creation and employee assignment to departments.
* **Positions**

  * Management of job positions.
* **Teams**

  * Employee grouping under leaders or managers.

### 🔹 Savings Module (Ahorro Gossler)

* Employee savings fund request and management.
* Request history.
* Admin panel to review and authorize requests.

### 🔹 Work Time

* Absence and vacation calendar.
* Automatic calculation of vacation days according to the Federal Labor Law (Mexico).

---

## 🖼️ User Interface

**General employee view:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/empleado_general.png)


**Personal notes:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/notas.png)

**Personal data (RFC, IMSS, CURP):**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/personal.png)

**Files and records:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/archivos.png)

**Departments and positions:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/areas_puestos.png)

**Work teams:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/equipos.png)

**Savings request:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/solicitud_ahorro.png)

**Savings admin panel:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/panel_ahorro.png)

**Vacation calendar:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/calendario.png)

**Anniversaries and absences table:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/aniversarios_ausencias.png)

**Global module settings:**
![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/docs/screenshots/configuraciones.png)

---

## ⚙️ Technical Requirements

* Nextcloud 28+
* PHP 8.1+
---
## 🧰 Installation Guide

1. Download the compiled version
Visit the latest release and download the .tar.gz file.

2. Extract and install
Place the extracted empleados folder into your Nextcloud custom_apps directory.

If your Nextcloud is in developer mode, run the following command:
php occ migrations:execute empleados *versions*

3. Assign the Data Manager
Go to the Configurations tab of the Employees module in the admin panel and set the Data Manager User.

![Image](https://github.com/user-attachments/assets/3a2888f6-1f94-480d-862b-fd85ef8afd64)

This user will store the employee documents in a folder called EMPLEADOS within their Nextcloud files. When activating a new employee, their personal folder is automatically created in that location.


---

## 🔄 Upcoming Roadmap

1. Complete the full vacations and absences workflow.
2. Downloadable reports in Excel/PDF.
3. Refined roles and permissions.
4. Develop technical and user manuals.
5. Prepare a stable, open-source release.

---
## 🔒 License

GNU Affero General Public License v3 (AGPL-3.0) — Free software for use, modification, and distribution under the terms of the license. Modifications and network interactions require source code disclosure.

---

## 🤝 Contributions

This module is intended for internal use and collaboration with other firms interested in customized Nextcloud ERP solutions.

Contributions and suggestions are welcome.

---

## 🚀 Current Status

* **Functional module** in internal production environment.
* **Public beta** in preparation.
* **Documentation in progress.**
