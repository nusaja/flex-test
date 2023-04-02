# Flexkeeping Test

In this project I wrote a PHP script that fetches data from the MEWS API and matches it with the data in an SQLite database.

## Installation

### Prerequisites

Project uses PHP and SQLite. 

### Getting Started

Copy config.php.example to config.php and edit it.

Create a new database file by running the following command in a terminal or command prompt: 

```
sqlite3 database.sqlite
```

After creating the database file, create a table like this:

```
CREATE TABLE rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_title TEXT NOT NULL,
    room_id TEXT
);
```

Next, insert your values with the following command: 

```
INSERT INTO rooms (room_title) VALUES ('yourvalue')
```

## Usage

Run the script with 

```
php console.php
```