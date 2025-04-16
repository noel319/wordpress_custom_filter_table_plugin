# Custom Filter Table Plugin - Usage Guide

This document provides instructions on how to use the Custom Filter Table plugin for WordPress.

## Overview

The Custom Filter Table plugin allows you to create filterable tables on your WordPress site. It's specifically designed to display data in a table format with filtering options, similar to the one shown in your screenshot.

## Plugin Features

- Filter functionality with dropdown menus and date pickers
- Responsive design that works on all devices
- Custom database table for storing data
- CSV import functionality
- Shortcode with customizable parameters
- Admin interface for managing entries
- Download functionality for documents

## Setting Up the Plugin

### Step 1: Installation

1. Upload the `custom-filter-table` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

### Step 2: Adding Data

There are two ways to add data to the table:

#### Option 1: Import from CSV

1. Go to Filter Table > Import CSV
2. Download the sample CSV file to see the required format
3. Prepare your CSV file with the same column structure
4. Upload and import your CSV file

#### Option 2: Add Entries Manually

1. Go to Filter Table > Add New Entry
2. Fill in the details for each field
3. Click "Publish" to save the entry

### Step 3: Configure Settings

1. Go to Filter Table > Settings
2. Set your preferred default table title
3. Configure the number of records to display per page
4. Save your settings

### Step 4: Display the Table on Your Site

Add the shortcode to any page or post where you want to display the table:

```
[custom_filter_table]
```

You can also customize the shortcode with parameters:

```
[custom_filter_table title="My Custom Table" municipio="Sorocaba/SP" situacao="Encerrada" limit="10"]
```

### Available Shortcode Parameters

| Parameter | Description | Example |
|-----------|-------------|---------|
| title | Custom table title | `title="My Custom Table"` |
| municipio | Filter by municipality | `municipio="Sorocaba/SP"` |
| situacao | Filter by status | `situacao="Encerrada"` |
| limit | Limit the number of results | `limit="10"` |

## How to Use the Filter Functionality

The table includes several filtering options:

1. **Município (Municipality)**: Select a municipality from the dropdown
2. **Situação (Status)**: Select a status from the dropdown
3. **Data da Publicação (Publication Date)**: Select a start date
4. **Data Final (End Date)**: Select an end date

When users change any of these filters, the table automatically updates to show only the matching records.

## Customizing the Plugin

### Styling

You can customize the appearance of the table by adding custom CSS to your theme. The main CSS classes used by the plugin are:

- `.custom-filter-table-container`: Main container
- `.filter-form-container`: Filter form area
- `.custom-filter-table`: The table itself
- `.download-button`: Download button styling

### Adding Custom Fields

If you need to add custom fields to the table:

1. Modify the database table structure in the activation function
2. Add the new fields to the meta box in the admin class
3. Update the CSV import function to handle the new fields
4. Update the table display in the shortcode class

## Troubleshooting

### Common Issues

- **Table not displaying**: Make sure you've added the shortcode correctly
- **Filters not working**: Check JavaScript console for errors
- **CSV import errors**: Ensure your CSV follows the required format
- **Missing download icons**: Check that the SVG file is in the correct location

### Getting Help

If you need further assistance:

1. Check the plugin documentation
2. Contact your plugin developer

## Conclusion

The Custom Filter Table plugin provides a powerful way to display filtered data on your WordPress site. With its easy-to-use interface and customization options, you can create professional-looking tables that meet your specific needs.