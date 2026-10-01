# Initial admin account on Render

The first administrator is created by `php artisan db:seed --force` when the backend container starts. To bootstrap it without committing credentials:

1. In the Render backend service, add `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD` under Environment.
2. Use a new email address and a unique password with at least 12 characters. Never put these values in Git or share the password in chat.
3. Deploy the backend and wait for the deploy to finish. The seeder creates the admin or promotes the account with that email, updating its name and password to the bootstrap values.
4. Sign in to the Android app with that email and password. The account opens the admin center because its role is `admin`.
5. Remove the three bootstrap variables from Render immediately after confirming login. While they remain set, every backend restart resets the admin name and password to those values. The admin role remains in the database after removing the variables.

Use a unique, strong password and rotate it if it has been shared or captured in a screenshot. Bootstrap credentials are secrets; do not commit or share them.
