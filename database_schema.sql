-- 1. Create/Use the Database
IF NOT EXISTS (SELECT * FROM sys.databases WHERE name = 'LRNPH_OJT')
BEGIN
    CREATE DATABASE LRNPH_OJT;
END
GO

USE LRNPH_OJT;
GO

-- 2. Create the FORMS Table 
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[dsp_forms]') AND type in (N'U'))
BEGIN
    CREATE TABLE dsp_forms (
        id INT IDENTITY(1,1) PRIMARY KEY, -- This creates your Control No (1, 2, 3...)
        full_name NVARCHAR(100),          -- Stores the creator's name
        department NVARCHAR(100),
        created_date DATETIME DEFAULT GETDATE(),
        status NVARCHAR(50) DEFAULT 'Pending',
        admin_approved_by NVARCHAR(50) NULL,
        admin_approved_date DATETIME NULL,
        dept_head_approved_by NVARCHAR(50) NULL,
        dept_head_approved_date DATETIME NULL,
        executive_approved_by NVARCHAR(50) NULL,
        executive_approved_date DATETIME NULL,
        final_dept_head_approved_by NVARCHAR(50) NULL,
        final_dept_head_approved_date DATETIME NULL
    );
END
GO

-- 3. Create the ITEMS Table 
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[dsp_items]') AND type in (N'U'))
BEGIN
    CREATE TABLE dsp_items (
        id INT IDENTITY(1,1) PRIMARY KEY,
        form_id INT,                      -- Links to dsp_forms.id
        code NVARCHAR(50),
        description NVARCHAR(255),
        serial_no NVARCHAR(100),
        unit_of_measure NVARCHAR(50),
        quantity DECIMAL(10, 2),
        reason NVARCHAR(MAX),             -- PHP uses 'reason' (check your code), previous script said 'reason_for_disposal'
        image_path NVARCHAR(MAX),         -- PHP uses 'image_path'
        CONSTRAINT FK_Form_Item FOREIGN KEY (form_id) REFERENCES dsp_forms(id) ON DELETE CASCADE
    );
END
GO
    
    -- Default Admin
    INSERT INTO lrnph_users (username, password, full_name, empcode, department, job_level, role) 
    VALUES ('admin', '12345', 'System Admin', '9999', 'IT Dept', 'Manager', 'Admin');
    
    -- Default Staff
    INSERT INTO lrnph_users (username, password, full_name, empcode, department, job_level, role) 
    VALUES ('staff', '12345', 'John Doe', '1001', 'Production', 'Staff', 'User');
END
GO