# Deals Manager Plugin Documentation

## Overview

Deals Manager is a complete Deals & Lead Management System and Sales CRM built to help businesses organize and track their sales pipeline, contacts, and tasks seamlessly inside WordPress.

## User Roles

The plugin introduces three user roles with specific capabilities:

*   **Administrator:** Has full access to all features of the plugin. Administrators can manage settings, create custom field groups, and view and manage all data (deals, contacts, companies, etc.) for all users.
*   **Manager:** Has access to all data within the Deals Manager. They can view and manage deals, contacts, companies, tasks, and invoices created by any user. This role is ideal for sales managers who need to oversee the entire team's activities.
*   **Sales Rep:** Has restricted access. Sales Reps can only create, view, and manage their own deals, contacts, companies, tasks, and invoices. They cannot see data created by other users.

## Core Features Guide

### Deals, Contacts, Companies, Tasks, & Invoices

These are all managed as Custom Post Types (CPTs) and can be found under the "Deals Manager" menu in the WordPress admin area. To create a new item, simply navigate to the appropriate section (e.g., Deals Manager -> Deals) and click "Add New". Fill in the fields in the meta boxes to add the relevant information.

### Pipeline / Kanban Board

The Kanban board provides a visual representation of your sales pipeline.

*   **Location:** Deals Manager -> Pipeline
*   **Functionality:** You can drag and drop deals from one stage to another to update their status in real-time.

### Reports & Analytics

The reports page provides insights into your sales performance.

*   **Location:** Deals Manager -> Reports
*   **Features:**
    *   **Deals by Stage:** A pie chart showing the distribution of your deals across the different pipeline stages.
    *   **Deals by User:** A table showing the number of deals assigned to each user.
    *   **Summary:** Key metrics including total deals, total value of won deals, and conversion rate.

### Custom Fields

You can create your own custom fields to tailor the plugin to your specific workflow.

*   **Location:** Deals Manager -> Custom Fields
*   **How to use:**
    1.  Click "Add New" to create a new Field Group.
    2.  Give the Field Group a title (e.g., "Additional Deal Information").
    3.  In the "Location" meta box, choose which post type (Deal, Contact, or Company) this field group should appear on.
    4.  In the "Fields" meta box, click "Add Field" to add a new custom field.
    5.  Configure the Field Label, Field Name, and Field Type for each field.
    6.  Publish the Field Group. The new fields will now appear on the edit screen of the selected post type.

### Sample Data

To help you get started and see the plugin in action, you can install sample data.

*   **Location:** Deals Manager -> Dashboard
*   **How to use:** Click the "Install Sample Data" button. This will create sample users, companies, contacts, deals, tasks, and invoices. This action can only be performed once.

### Lead Capture Shortcode

To capture leads from the frontend of your website, you can use the following shortcode:

*   **Shortcode:** `[dm_lead_form]`
*   **How to use:** Place this shortcode on any WordPress page or post. It will display a lead capture form. When a visitor submits the form, a new Contact and a new Deal (in the 'Lead' stage) will be automatically created in the system.
