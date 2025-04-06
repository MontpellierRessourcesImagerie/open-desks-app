# Open-desks booking application

- This application allows users to book an appointment to the open-desk sessions proposed by MRI-CIA.
- It offers a user-friendly interface, working on desktop and mobile, referencing the incoming dates.
- Users can provide their contact information as well as a description of their problem and a few images.
- The data provided by the users can only be seen by connected engineers.
- Anybody can request the creation of an account, but the request has to be approved before the new account can be used.

---

## Install the application

- Copy all the files except `od-reset-tables.sql` and `to-do.txt` to the server's folder.
- Update de database's name in `od-reset-tables.sql`.
- Inject the content of `od-reset-tables.sql` in the database.
- Update the content of `db-config.php` with the correct database, user and password.
- Go right away on the "signup.php" page and create an account.

## Booking

- Anybody (no connection required) can book an appointment for a session from the main page (`index.php`).
- This page can be reached from anybody with the URL, it doesn't require authentication.
- The page provides a short description of what open-desks are, and what they consist in.
- Then, a carousel is present, containing all the upcoming sessions. Passed sessions are not shown to the users.
- The last part is a form allowing the user to book an appointment for a session. There is the possibility to provide a link to some images, or to come with an external drive. If a user doesn't provide a link to some data, he will have to check the "USB drive" checkbox.
- If a user doesn't check the "I'm from the Route de Mende campu", he will be prompted a warning to get a QR-code (with a link).
- A footer is present containing useful links (MRI website, project form, QR-codes website, and the control-panel).

## Control-panel

- A link to this page is present in the footer of the main page.
- Only authentified engineers can reach this page.
    - To create an account, go to the "sign up" page, and choose a username and a password. Then, you need to wait for a person with a valid account to validate your request.
    - Once your request was accepted, you can go back to the "connect" page.
    - A connection is only valid for hours, and only one device can be connected at a time.
- From the header, you can download a CSV file containing a summary of all the appointments present in the database. It is also where you can log-out.
- The first block contains the active sessions. If you click on any of them, you will see the list of appointments in the block below.
- The third block allows to add "locations", which are where a session can take place. It should consist in the name of the room, followed by the building's name.
- The fourth block allows to add new sessions that will be proposed to the users.
- Eventually, the last block allows to manage "connected engineers". You can "revoke" an access (== prevent access but not delete the account) or delete it.

## SQL Tables

- The tables are available in the `od-reset-tables.sql` file.
- An exemple of data-base configuration file is present in `db-config.php`.

## Update

- Copy the files except `db-config.php`, `od-reset-tables.sql` and `to-do.txt`.
- Launch the SQL commands: 
```sql
ALTER TABLE appointments ADD COLUMN has_come BOOLEAN NOT NULL DEFAULT FALSE;
UPDATE appointments SET has_come = TRUE;
ALTER TABLE appointments ADD COLUMN cancel_id VARCHAR(6) NOT NULL;
ALTER TABLE appointments ADD COLUMN canceled BOOLEAN NOT NULL DEFAULT FALSE;
```
