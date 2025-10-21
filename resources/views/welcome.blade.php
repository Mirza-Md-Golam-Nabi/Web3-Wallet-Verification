<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Web3 Wallet Verification - BlockVerse Solutions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .step {
            margin-bottom: 20px;
            padding: 15px;
            border-left: 4px solid #007bff;
            background-color: #f8f9fa;
        }
        .step.completed {
            border-left-color: #28a745;
            background-color: #d4edda;
        }
        .step.active {
            border-left-color: #ffc107;
            background-color: #fff3cd;
        }
        .status-badge {
            font-size: 0.8rem;
            margin-left: 10px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="text-center">Web3 Wallet Verification</h1>
        <p class="text-center text-muted">BlockVerse Solutions</p>

        <!-- Step 1: Connect Wallet -->
        <div class="step active" id="step1">
            <h5>Step 1: Connect Your Wallet</h5>
            <p>Connect your MetaMask wallet to get started.</p>
            <button type="button" class="btn btn-primary" id="connectWallet">
                Connect Wallet
            </button>
        </div>

        <!-- Step 2: Display Wallet Address -->
        <div class="step" id="step2">
            <h5>Step 2: Wallet Connected</h5>
            <div id="walletInfo" style="display: none;">
                <p><strong>Wallet Address:</strong> <span id="walletAddress"></span></p>
                <p><strong>Balance:</strong> <span id="walletBalance"></span> ETH</p>
            </div>
        </div>

        <!-- Step 3: Sign Message -->
        <div class="step" id="step3">
            <h5>Step 3: Sign Message</h5>
            <p>Sign the verification message to prove wallet ownership.</p>
            <div class="alert alert-info">
                <strong>Message to sign:</strong><br>
                <code>"Verifying wallet for BlockVerse Solutions"</code>
            </div>
            <button type="button" class="btn btn-warning" id="signMessage" disabled>
                Sign Message
            </button>
        </div>

        <!-- Step 4: Verification Result -->
        <div class="step" id="step4">
            <h5>Step 4: Verification Result</h5>
            <div id="verificationResult" style="display: none;">
                <p><strong>Status:</strong> <span id="verificationStatus"></span></p>
                <p><strong>Message:</strong> <span id="verificationMessage"></span></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="text-center mt-4 mb-4">
            <button type="button" class="btn btn-secondary" id="resetBtn" style="display: none;">
                Reset
            </button>
        </div>

        <!-- Error Message -->
        <div id="errorMessage" class="alert alert-danger mt-3" style="display: none;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>

    <script>
        // DOM Elements
        const connectWalletBtn = document.getElementById('connectWallet');
        const signMessageBtn = document.getElementById('signMessage');
        const resetBtn = document.getElementById('resetBtn');
        const walletInfo = document.getElementById('walletInfo');
        const verificationResult = document.getElementById('verificationResult');
        const errorMessage = document.getElementById('errorMessage');

        // Steps
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        const step4 = document.getElementById('step4');

        let currentAddress = '';

        // 1. Connect Wallet Function
        async function connectWallet() {
            try {
                showError('');

                if (typeof window.ethereum === 'undefined') {
                    throw new Error('MetaMask is not installed! Please install MetaMask first.');
                }

                const accounts = await window.ethereum.request({
                    method: 'eth_requestAccounts'
                });

                currentAddress = accounts[0];

                // Get wallet balance
                const balance = await getWalletBalance(currentAddress);

                // Display wallet info
                displayWalletInfo(currentAddress, balance);

                // Update steps
                updateStep(1, 'completed');
                updateStep(2, 'active');
                updateStep(3, 'active');

                // Enable sign message button
                signMessageBtn.disabled = false;

                console.log('Wallet connected:', currentAddress);

            } catch (error) {
                console.error('Wallet connection failed:', error);
                if (error.code === 4001) {
                    showError('User rejected the connection request.');
                } else {
                    showError('Connection failed: ' + error.message);
                }
            }
        }

        // 2. Sign Message Function
        async function signMessage() {
            try {
                showError('');
                signMessageBtn.disabled = true;
                signMessageBtn.textContent = 'Signing...';

                const message = "Verifying wallet for BlockVerse Solutions";

                console.log('Signing message:', message);

                const signature = await window.ethereum.request({
                    method: 'personal_sign',
                    params: [message, currentAddress]
                });

                console.log('Signature received:', signature);

                // Send to backend for verification
                await verifySignature(currentAddress, signature, message);

            } catch (error) {
                console.error('Message signing failed:', error);
                signMessageBtn.disabled = false;
                signMessageBtn.textContent = 'Sign Message';

                if (error.code === 4001) {
                    showError('User rejected the signature request.');
                } else {
                    showError('Signing failed: ' + error.message);
                }
            }
        }

        // 3. Verify Signature with Backend
        async function verifySignature(address, signature, message) {
            try {
                console.log('Sending verification request to backend...');

                const response = await fetch('/metamask/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        wallet_address: address,
                        signature: signature,
                        message: message
                    })
                });

                const result = await response.json();
                console.log('Verification result:', result);

                // Display verification result
                displayVerificationResult(result);

                // Update steps
                updateStep(3, 'completed');
                updateStep(4, 'active');

                // Show reset button
                resetBtn.style.display = 'inline-block';

            } catch (error) {
                console.error('Verification request failed:', error);
                showError('Verification failed: ' + error.message);

                // Re-enable sign button
                signMessageBtn.disabled = false;
                signMessageBtn.textContent = 'Sign Message';
            }
        }

        // Helper Functions
        async function getWalletBalance(address) {
            try {
                const balance = await window.ethereum.request({
                    method: 'eth_getBalance',
                    params: [address, 'latest']
                });
                return (parseInt(balance) / 1e18).toFixed(4);
            } catch (error) {
                console.error('Balance fetch failed:', error);
                return '0.0000';
            }
        }

        function displayWalletInfo(address, balance) {
            // const shortAddress = `${address.substring(0, 6)}...${address.substring(address.length - 4)}`;

            document.getElementById('walletAddress').textContent = address;
            document.getElementById('walletAddress').title = address; // Full address on hover
            document.getElementById('walletBalance').textContent = balance;

            walletInfo.style.display = 'block';
        }

        function displayVerificationResult(result) {
            const statusElement = document.getElementById('verificationStatus');
            const messageElement = document.getElementById('verificationMessage');

            if (result.success) {
                statusElement.textContent = '✅ TRUE';
                statusElement.className = 'text-success';
                messageElement.textContent = result.message || 'Signature verified successfully!';
                messageElement.className = 'text-success';
            } else {
                statusElement.textContent = '❌ FALSE';
                statusElement.className = 'text-danger';
                messageElement.textContent = result.message || 'Signature verification failed!';
                messageElement.className = 'text-danger';
            }

            verificationResult.style.display = 'block';
        }

        function updateStep(stepNumber, status) {
            const step = document.getElementById(`step${stepNumber}`);
            step.className = `step ${status}`;
        }

        function showError(message) {
            if (message) {
                errorMessage.textContent = message;
                errorMessage.style.display = 'block';
            } else {
                errorMessage.style.display = 'none';
            }
        }

        function resetFlow() {
            // Reset all steps
            updateStep(1, 'active');
            updateStep(2, '');
            updateStep(3, '');
            updateStep(4, '');

            // Reset UI elements
            walletInfo.style.display = 'none';
            verificationResult.style.display = 'none';
            signMessageBtn.disabled = true;
            signMessageBtn.textContent = 'Sign Message';
            resetBtn.style.display = 'none';
            showError('');

            currentAddress = '';
        }

        // Event Listeners
        connectWalletBtn.addEventListener('click', connectWallet);
        signMessageBtn.addEventListener('click', signMessage);
        resetBtn.addEventListener('click', resetFlow);

        // Auto-connect if already connected
        window.addEventListener('load', async () => {
            if (typeof window.ethereum !== 'undefined') {
                try {
                    const accounts = await window.ethereum.request({
                        method: 'eth_accounts'
                    });

                    if (accounts.length > 0) {
                        currentAddress = accounts[0];
                        const balance = await getWalletBalance(currentAddress);
                        displayWalletInfo(currentAddress, balance);

                        updateStep(1, 'completed');
                        updateStep(2, 'active');
                        updateStep(3, 'active');
                        signMessageBtn.disabled = false;
                    }
                } catch (error) {
                    console.error('Auto-connect failed:', error);
                }
            }
        });

        // Handle account changes
        if (window.ethereum) {
            window.ethereum.on('accountsChanged', (accounts) => {
                if (accounts.length === 0) {
                    resetFlow();
                    showError('Wallet disconnected');
                } else {
                    resetFlow();
                    connectWallet();
                }
            });
        }
    </script>
</body>

</html>
