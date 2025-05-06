# Invoice Filament

A Laravel-based application built with Filament for managing tasks, contracts, and related resources.

## Features
- Manage tasks with detailed views and editing capabilities.
- Filter and sort tasks by customer, name, hours, and status.
- Bulk actions such as delete and deactivate tasks.
- Create tasks linked to contracts and associate with users.
- Custom query scope to include task hours sum.

## Main Components
- **TaskResource**: Handles task management, including forms, tables, and related actions.
- **CreateTaskAction**: Custom action to handle task creation.
- **Task Model**: Represents tasks in the database, associated with contracts and users.

## Usage
- Access task list via the main menu.
- Use filters and bulk actions for efficient management.
- Create or edit tasks through modal forms.

## Requirements
- Laravel v12
- PHP 8.4+
- Database: PostgreSQL
- Queue system configured

---

For more details, refer to the codebase or contact the maintainer.
