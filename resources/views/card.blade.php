<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clover Card Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
        }

        .card-form label {
            display: block;
            margin-bottom: 8px;
        }

        .card-form input {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .card-form button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .card-form button:hover {
            background-color: #45a049;
        }
    </style>
     <script src="https://checkout.sandbox.dev.clover.com/sdk.js"></script>

</head>

<body>

    <div class="card-form">
        <h2>Enter Card Details</h2>
        <form id="card-form">
            <label for="card-number">Card Number</label>
            <input type="text" id="card-number" max="16" placeholder="1234 5678 9101 1121" required>

            <label for="exp-month">Expiry Month</label>
            <input type="text" id="exp-month" placeholder="MM" required>

            <label for="exp-year">Expiry Year</label>
            <input type="text" id="exp-year" placeholder="YYYY" required>

            <label for="cvv">CVV</label>
            <input type="text" id="cvv" placeholder="123" required>

            <button type="submit">Submit</button>
        </form>
    </div>

    <!-- Clover SDK -->

    <script>
        const clover = new Clover('82596c3556b41dcc50cf157869caa1a4'); // Initialize Clover with your public key

        document.getElementById('card-form').addEventListener('submit', function (event) {
            event.preventDefault();

            const cardDetails = {
                card: {
                    number: document.getElementById('card-number').value,
                    exp_month: document.getElementById('exp-month').value,
                    exp_year: document.getElementById('exp-year').value,
                    cvv: document.getElementById('cvv').value
                }
            };
            console.log(cardDetails,'cardDetails');

            clover.tokens.create(cardDetails)
            .then(function (result) {
                if (result && result.token) {
                    console.log('Token:', result.token);
                    alert('Card token created: ' + result.token);
                } else {
                    console.error('Error:', result.error);
                    alert('Error creating token: ' + result.error.message);
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
            });

        });
    </script>

</body>
</html>
