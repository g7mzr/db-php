---
layout: default
title: Home
---
# Home

## Introduction

**This documentation is currently under development.**

**db-php** is a composer module for *php 7.2* and above for accessing Postgresql Databases
using *php*.  It started out as part of the "webtemplate" application but developed into
a stand alone module.

It is hoped that it will be extended to allow access to a MySQL database at a later date.

## Overview

The full functionality of **db-php** is accessed using dbmanager.php.  This allows access
to the three modules independently of each other.  The three modules are:
* Admin - Used to manage RBMS users and blank databases
* Schema - Used to create and modify the database schema
* DataAccess - Used to Select, Insert, Delete and Update information in the database.

Only one module can be activated at a time and if you attempt to access the wrong one
the software will throw an exception.


