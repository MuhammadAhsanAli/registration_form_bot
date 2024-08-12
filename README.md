# Registration Form Bot

## Introduction

The Registration Form Bot is a project designed to automate user registration processes and handle CAPTCHA challenges dynamically. This tool is useful for scenarios where bulk user registrations are required, and CAPTCHA solutions are necessary to bypass automated blocks. It also includes functionality to automatically fetch OTP (One-Time Password) from emails.

## Use Case

- **Automated User Registration**: Streamline the registration process by automating the submission of user credentials.
- **CAPTCHA Bypass**: Automatically solve CAPTCHA challenges to facilitate uninterrupted registration.
- **OTP Retrieval**: Automatically fetch OTP from emails to complete the registration process.
- **Flexible Configuration**: Easily configure and run the bot with different email providers.

## Installation Steps

1. **Clone the Repository**

    ```bash
    git clone https://github.com/MuhammadAhsanAli/registration_form_bot.git
    ```

2. **Create the `.env` File**

   Copy the example environment file and create a `.env` file.

    ```bash
    cp .env.example .env
    ```

3. **Set Environment Variables**

   Open the `.env` file and set the following variables:

    - **Database Credentials**: Set your database connection credentials.
    - **CAPTCHA_API_KEY**: Your 2CAPTCHA service API key.
    - **CAPTCHA_API_URL**: The URL of your 2CAPTCHA service.
    - **EMAIL_USERNAME**: Your email address for OTP retrieval.
    - **EMAIL_PASSWORD**: The password for your email account.

4. **Build and Run Docker Containers**

    ```bash
    docker-compose build
    docker-compose up -d
    ```

5. **Access the Docker Container**

    ```bash
    docker-compose exec app bash
    ```

6. **Generate Application Key**

   Inside the Docker container, run:

    ```bash
    php artisan key:generate
    ```

7. **Configure Email IDs and App Passwords**

   Edit the `RegisterUser.php` file to add email IDs and app passwords for IMAP access.

    ```bash
    nano /var/www/app/Console/Commands/RegisterUser.php
    ```

8. **Run the Registration Command**

   Execute the registration command to start the user registration process:

    ```bash
    php artisan app:register-user
    ```

   This command will handle the following:
    - Register users with the provided credentials.
    - Automatically solve CAPTCHA challenges.
    - Fetch OTP from emails to complete the registration process.

