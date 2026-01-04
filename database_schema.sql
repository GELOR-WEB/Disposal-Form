-- 1. Create Database (if not exists)
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'GMP')
BEGIN
    CREATE DATABASE GMP;
END
GO

USE GMP;
GO

-- 2. Create the Forms Table (Fixes "Invalid object name 'disposal_forms'")
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[disposal_forms]') AND type in (N'U'))
BEGIN
    CREATE TABLE disposal_forms (
        id INT IDENTITY(1,1) PRIMARY KEY,
        user_id INT,
        full_name NVARCHAR(100),
        department NVARCHAR(100),
        created_date DATETIME DEFAULT GETDATE(),
        status NVARCHAR(50) DEFAULT 'Pending',
        approved_by INT NULL,
        approved_date DATETIME NULL
    );
END

-- 3. Create the Items Table
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[disposal_items]') AND type in (N'U'))
BEGIN
    CREATE TABLE disposal_items (
        id INT IDENTITY(1,1) PRIMARY KEY,
        form_id INT,
        code NVARCHAR(50),
        description NVARCHAR(255),
        serial_no NVARCHAR(100),
        unit_of_measure NVARCHAR(50),
        quantity DECIMAL(10, 2),
        reason_for_disposal NVARCHAR(MAX),
        attachment_pictures NVARCHAR(MAX),
        CONSTRAINT FK_Form_Item FOREIGN KEY (form_id) REFERENCES disposal_forms(id)
    );
END

-- 4. Create the Master List (Employees)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[lrn_master_list]') AND type in (N'U'))
BEGIN
    CREATE TABLE lrn_master_list (
        id INT IDENTITY(1,1) PRIMARY KEY,
        EmployeeID NVARCHAR(50), 
        FullName NVARCHAR(100),
        JobLevel NVARCHAR(50),
        Department NVARCHAR(100)
    );
    INSERT INTO lrn_master_list (EmployeeID, FullName, JobLevel, Department) VALUES ('1001', 'Angelor De Jesus', 'Supervisor', 'IT Dept');
    INSERT INTO lrn_master_list (EmployeeID, FullName, JobLevel, Department) VALUES ('1002', 'John Staff', 'Staff', 'IT Dept');
END

-- 5. Create Accounts (Login)
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[accounts]') AND type in (N'U'))
BEGIN
    CREATE TABLE accounts (
        AccountID INT IDENTITY(1,1) PRIMARY KEY,
        EmployeeID INT, 
        Username NVARCHAR(50),
        Password NVARCHAR(50)
    );
    INSERT INTO accounts (EmployeeID, Username, Password) VALUES (1, 'admin', 'password123');
    INSERT INTO accounts (EmployeeID, Username, Password) VALUES (2, 'staff', 'password123');
END
GO