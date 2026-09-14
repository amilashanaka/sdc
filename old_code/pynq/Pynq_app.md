# Project Structure

## List Of Modules

|--- Main

|--- Daq

|--- UDP

|--- ErrorCode

|--- IPC

|--- Tcp

|--- Config

---

# Main

1. Listen  to conection request 

2. Authentication 

3. execute incomming commands and reply with data 

4. 

---

# CLI

1. listen to incomming commands 

2. decoe the commands and execute 

3. develop the result 

4. 







---



### Setting up Host Name

# ✅ **1. Temporary Hostname (until reboot)**

`sudo hostname NEWNAME`

Example:

`sudo hostname pynq-daq`

This takes effect immediately **but will reset after reboot**.

---

# ✅ **2. Permanent Hostname (survives reboot)**

## **Step 1 — Edit `/etc/hostname`**

`sudo nano /etc/hostname`

Replace the existing name with:

`NEWNAME`

Save (Ctrl+O), exit (Ctrl+X).

---

## **Step 2 — Edit `/etc/hosts`**

This maps the hostname for local networking.

`sudo nano /etc/hosts`

Find the line:

`127.0.1.1    old-hostname`

Change to:

`127.0.1.1    NEWNAME`

---

## **Step 3 — Reboot**

`sudo reboot`

Now the board will come back up with the new hostname.

---

# 🧪 **Check Hostname**

`hostname`

or

`uname -n`

-----
