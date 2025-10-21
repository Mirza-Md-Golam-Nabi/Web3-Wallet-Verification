# Web3 Wallet Verification

- [Requirement](#requirement)
- [✅ Project Setup Instructions](#-project-setup-instructions)
- [Challenges and Solutions](#challenges-and-solutions)
- [Workflow Overview](#workflow-overview)

## Requirement

-   PHP version: 8.2

## ✅ Project Setup Instructions

1. Clone the repository

For Local Machine:

```sh
git clone https://github.com/Mirza-Md-Golam-Nabi/Web3-Wallet-Verification.git
```

2. Goto project folder

```sh
cd Web3-Wallet-Verification
```

3. Install dependencies using Composer

First, you check GMP extension is enable or not.

```sh
php -m | grep gmp
```

- If the output displays the text **gmp**, the extension is already enabled and no further action is needed.
- If the command returns no output, the GMP extension is not currently enabled. Please proceed to Step 2.
- Locate and open your **php.ini** configuration file.
- Within the file, search for the line containing **extension=gmp**.
- If this line is prefixed with a semicolon (;), it is commented out. Remove the semicolon to uncomment and activate the line.
  - Before: ;extension=gmp
  - After: extension=gmp
- Save the **php.ini** file.

**N.B.** Restart your web server (e.g., Apache, Nginx) and any related services (e.g., Laragon, XAMPP) for the changes to take effect.

Now run this composer command:

```sh
composer install
```

4. Create the **.env** file

Copy the example environment file:

```sh
cp .env.example .env
```

5. Run this command:

```sh
php artisan key:generate
```

6. Create the database

Create a database named:

```sh
block_chain
```

7. Run migrations and seeders

Run the following command to migrate and seed the database:

```sh
php artisan migrate:fresh
```

8. Run the application

```sh
npm install && npm run build
```

and

```sh
php artisan serve
```

## Challenges and Solutions

Since **MetaMask Wallet** was completely new to me, I initially faced some challenges understanding its documentation. After installing the **MetaMask** browser extension and completing the registration process, I encountered difficulties logging into the wallet and connecting it properly.

To resolve these issues, I sought help from **YouTube** tutorials, which guided me through the process. After several attempts and some practice, I was finally able to fix the problem and connect the wallet successfully.

Throughout the development of this project, I took reference and guidance from:

- MetaMask official documentation
- DeepSeek AI, and
- A technical blog page

## Workflow Overview

At first, the user connects to the MetaMask wallet by clicking the “**Connect Wallet**” button. Once connected, MetaMask provides the user’s **wallet address**, which can also be used to retrieve the **wallet’s balance**. This process is illustrated in **Step 2: Wallet Connected**.

Next, the “**Sign Message**” button is used to verify the ownership of the connected wallet. When MetaMask returns a successful response, the verification result is displayed in **Step 4**.

If the wallet is not connected, Step 3 (signing functionality) remains disabled.
Finally, a **Reset** button is provided to clear the session and start the process again.
