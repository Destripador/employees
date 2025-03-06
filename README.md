# 🚀 Employee Management Module for Nextcloud (ON DEV)

## 📌 Description

The **Employee Module** is an administrative solution designed for **employee management and information handling**. This module seamlessly integrates with Nextcloud to provide businesses and organizations with an **efficient and secure** way to manage employees, roles, and records within their existing Nextcloud infrastructure.



![alt text](https://raw.githubusercontent.com/Destripador/employees/refs/heads/main/img/image1.png)


## 🎯 Features

✔ **Employee Directory** – Maintain a structured and easily searchable list.  
✔ **Role and Department Management** – Assign employees to departments and define roles.  
✔ **User Authentication Integration** – Sync with Nextcloud users.  
✔ **Contract and Payroll Management** – Store key employee contract information.  
✔ **Attendance and Leave Tracking** – Monitor working hours and absences.  
✔ **Hierarchical Organization Chart** – Visualize employee reporting structures.  
✔ **Secure Document Storage** – Upload and manage employee-related files.  
✔ **Performance Evaluation** – Track employee reviews and assessments.  
✔ **Notifications and Alerts** – Get updates on employee-related actions.  
✔ **Admin Dashboard** – Intuitive interface for efficient employee management.  

---

## 📂 Installation

You can install this module in your Nextcloud instance manually or using `occ` commands.

### **Using the Nextcloud App Store**
1. Go to the **Nextcloud App Store**.
2. Search for `"Employee Management"`.
3. Click **Install** and enable the app from the Nextcloud Admin Panel.

### **Manual Installation**
1. Navigate to the Nextcloud apps directory:
   ```sh
   cd /path/to/nextcloud/apps
   ```
2. Clone the repository:
   ```sh
   git clone https://github.com/Destripador/employees.git employee_management
   ```

---

## 🏗 Building the App

If you are developing and need to build the app, follow these steps:

1. Install dependencies:
   ```sh
   npm install
   ```
2. Build the frontend assets:
   ```sh
   npm run build
   ```
3. Clear Nextcloud's cache:
   ```sh
   sudo -u www-data php occ maintenance:repair
   ```
4. Enable the app (if not enabled):
   ```sh
   sudo -u www-data php occ app:enable employee_management
   ```

---

## 🧪 Running Tests

To run unit tests, ensure you have PHPUnit installed:

```sh
php vendor/bin/phpunit tests
```

To run JavaScript tests:

```sh
npm run test
```

For linting and code style checks:

```sh
npm run lint
```

---

## 🤝 How to Contribute

We welcome contributions! Follow these steps to submit a Pull Request:

### **How to Create a Pull Request**
1. **Fork** this repository on GitHub.
2. **Clone** your fork locally:
   ```sh
   git clone https://github.com/Destripador/employees.git
   ```
3. Create a **feature branch**:
   ```sh
   git checkout -b feature/my-new-feature
   ```
4. Make your changes, commit them, and push the branch:
   ```sh
   git add .
   git commit -m "Add new feature XYZ"
   git push origin feature/my-new-feature
   ```
5. Open a **Pull Request** on GitHub.

---

## 🌟 Code of Conduct

We expect all contributors to follow our **Code of Conduct**:

1. Be respectful to others.
2. Provide constructive feedback.
3. Follow Nextcloud’s security and privacy guidelines.
4. Ensure your contributions align with the project's goals.

For details, refer to [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

---

## 📄 License

This project is licensed under the **MIT License** – see the [LICENSE](LICENSE) file for more details.

---

### 🌟 If you find this project useful, don't forget to give ⭐ to the repository!

---

## 🔗 Resources
- [Nextcloud Developer Documentation](https://nextcloud.com/developer/)
- [Nextcloud App Store](https://apps.nextcloud.com/)
- [Nextcloud Forum](https://help.nextcloud.com/)

