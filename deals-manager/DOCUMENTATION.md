# Deals Manager - User Guide

## 1. Getting Started

### Installation

1.  Download the `deals-manager.zip` file.
2.  In your WordPress admin dashboard, navigate to **Plugins > Add New**.
3.  Click **Upload Plugin** and select the `deals-manager.zip` file.
4.  Click **Install Now** and then **Activate Plugin**.

### First Steps

Once activated, you will see a new "Deals Manager" menu item in your WordPress admin sidebar.

*   **Dashboard:** The main dashboard gives you an overview of the plugin and provides access to key features.
*   **Sample Data:** To help you get familiar with the plugin, you can install sample data. Navigate to **Deals Manager > Dashboard** and click the "Install Sample Data" button. This will create sample deals, contacts, companies, tasks, and users.

---

## 2. Core Features Guide

### Deals

Deals are the core of your sales pipeline.

*   **To create a deal:** Navigate to **Deals Manager > Deals** and click "Add New".
*   **Deal Details:**
    *   **Related Contact:** Link the deal to a contact from your contacts list.
    *   **Value:** The potential monetary value of the deal.
    *   **Owner:** The user responsible for the deal.
    *   **Priority:** Set the priority (Low, Normal, High).
    *   **Stage:** The current stage of the deal in your pipeline (Lead, Proposal, Negotiation, Won, Lost).
*   **Activity:** View a complete history of all activities related to the deal, including notes, calls, emails, and stage changes. You can also add new activities here.

### Pipeline / Kanban Board

The Kanban board provides a visual, drag-and-drop interface for managing your sales pipeline.

*   **Location:** Deals Manager -> Pipeline
*   **How to use:** Simply drag a deal card from one column and drop it into another to update its stage. The change is saved automatically.

### Contacts & Companies

Manage your leads and customer information.

*   **To create a contact or company:** Navigate to **Deals Manager > Contacts** or **Deals Manager > Companies** and click "Add New".
*   Fill in the standard fields (Email, Phone, Website, Address). You can also link contacts and companies together.

### Tasks

Create and manage tasks to stay on top of your sales activities.

*   **To create a task:** Navigate to **Deals Manager > Tasks** and click "Add New".
*   **Task Details:**
    *   **Due Date:** Set a due date for the task.
    *   **Status:** Set the status (To Do, In Progress, Completed).
    *   **Related To:** Link the task to a specific Deal, Contact, or Company.

### Invoices

Create and manage invoices for your deals.

*   **To create an invoice:** Navigate to **Deals Manager > Invoices** and click "Add New".
*   **Line Items:** Use the "Add Item" button to add multiple line items to your invoice. The "Price" and "Quantity" fields will automatically calculate the total for each line and the subtotal for the invoice.
*   **Printing:** To print an invoice, click the "View" link under an invoice in the list table. On the public invoice page, click the "Print Invoice" button.

---

## 3. Advanced Features

### Custom Fields

Customize the plugin by adding your own fields to Deals, Contacts, and Companies.

*   **Location:** Deals Manager -> Custom Fields
*   **How to create a field group:**
    1.  Click "Add New" to create a new Field Group.
    2.  Give the Field Group a title (e.g., "Additional Deal Information").
    3.  In the "Location" meta box, choose which post type (Deal, Contact, or Company) this field group should appear on.
    4.  In the "Fields" meta box, click "Add Field".
    5.  Configure the Field Label, Field Name, and Field Type (Text, Text Area, or Number). The Field Name is generated automatically but can be customized.
    6.  Publish the Field Group. The new fields will now appear in a new meta box on the edit screen of the selected post type.

### Lead Capture Shortcode

Capture leads directly from your website's frontend.

*   **Shortcode:** `[dm_lead_form]`
*   **How to use:** Place this shortcode on any WordPress page or post. It will display a lead capture form.
*   **Lead Tracking:** To track which user or campaign a lead came from, you can add a `ref` parameter to the URL of the page where the form is located.
    *   **Example:** `https://yourwebsite.com/contact/?ref=johnsmith`
    *   When a visitor submits the form on this page, the new Deal and Contact will be assigned to the user with the username `johnsmith`.

### Reporting & Exporting

*   **Reports:** Navigate to **Deals Manager > Reports** to see charts and summaries of your sales data.
*   **Export to CSV:** On the list pages for Deals, Contacts, and Companies, click the "Export to CSV" button to download all data for that post type.
*   **Print Deals:** Navigate to **Deals Manager > Print Deals**. Use the filters to select a date range and/or user, then click "Generate Report" to open a printer-friendly list of the matching deals.

---

## 4. User Roles & Permissions

*   **Administrator:** Full access to everything.
*   **Manager:** Can view and manage all data for all users.
*   **Sales Rep:** Can only view and manage their own data.
