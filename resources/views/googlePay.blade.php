<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>GPay and Hosted Checkout</title>
    <style>
      body {
        background-color: white;
        margin: 0 auto;
        width: 300px;
      }

      .container {
        /* background-color: #ffffff; */
        /* box-shadow: 0 0 6px 0 rgba(141, 151, 158, 0.2); */
        /* padding: 24px; */
      }

      .container * {
        font-family: Roboto, "Open Sans", sans-serif;
        font-size: 16px;
      }

      .container .form-row {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-between;
      }

      .container .form-row.top-row {
        margin-top: 16px;
      }

      .input-errors {
        /* font-size: 12px;
        position: absolute;
        margin-left: 5px;
        margin-top: 54px;
        color: red; */
      }

      .container .form-row .field {
        /* box-sizing: border-box;
        border: 1px solid #dee0e1;
        border-radius: 3px;
        height: 55px;
        margin-bottom: 30px;
        padding: 14px;
        width: 100%; */
      }

      .container .button-container {
        /* display: flex;
        flex-direction: row;
        justify-content: center; */
      }


      #payment-request-button {
        width: 300px;
        height: 40px;
        margin: 0 auto;
      }

      @media (min-width: 300px) {
        body {
          width: auto;
        }
      }

      @media (min-width: 750px) {
        body {
          width: 400px;
        }

        .container {
         /* height: 490px; */
        }

        .container .form-row .field.full-width {
          width: 460px;
        }

        .container .form-row .field.third-width {
          width: 218px;
        }
      }

      .clover-privacy-link{
            display: none !important;
      }
      .clover-secure-payments{
            display: none !important;
      }
      .clover-footer{
            display: none !important;
            /* padding: 8px 24px !important; */
            background-color: white !important;
            border-radius: 0 0 0px 0px !important;
      }
    </style>
    <script src="https://checkout.clover.com/sdk.js"></script>
    {{-- <script src="https://checkout.sandbox.dev.clover.com/sdk.js"></script> --}}
  </head>
  <body>
    <div class="container">
      <form action="/charge" method="post" id="payment-form">
        <div class="form-row top-row">
          <div
            id="payment-request-button"
            class="payment-request-button full-width"
          ></div>
        </div>
      </form>
      {{-- <button onclick="cloverTokenHandler('teststtst')" class="btn btn-secondary">Test</button> --}}
    </div>
    <style>
     .gpay-button{
            border-radius: 10px !important;
      }
      #gpay-button-online-api-id{
            border-radius: 10px !important;

      }
    </style>

    <script>
      const clover = new Clover('41e1502a27e2f42c6647a17d40679ca1');
      const elements = clover.elements();
      const form = document.getElementById("payment-form");

      form.addEventListener("submit", function (event) {
        event.preventDefault();

        clover
          .createToken()
          .then(function (result) {
            if (result.errors) {
              Object.values(result.errors).forEach(function (value) {
                console.log(value,'error in the field');
              });
            } else {
                cloverTokenHandler(result.token);
            }
          })
          .catch(function (data) {
            console.log(data);
          });
      });

     const main_amount = {{ $amount }};

      const paymentReqData = {
        total: {
          label: 'Demo total',
          amount: main_amount,
        },
        options: {
          button: {
            buttonType: "short",
            buttonColor: "black",
            buttonLocale: "en",
          },
        },
      };


      const paymentRequestButton = elements.create("PAYMENT_REQUEST_BUTTON", {
        paymentReqData,
      });
      paymentRequestButton.mount("#payment-request-button");

      paymentRequestButton.addEventListener("paymentMethod", function (ev) {
         cloverTokenHandler(ev.token)
      });

      function cloverTokenHandler(token) {

        var form = document.getElementById("payment-form");
        var hiddenInput = document.createElement("input");
        hiddenInput.setAttribute("type", "hidden");
        hiddenInput.setAttribute("name", "cloverToken");
        hiddenInput.setAttribute("value", token);
        form.appendChild(hiddenInput);

        const payload = {
                _token: '{{ csrf_token() }}', // Include CSRF token for security
                cloverToken: token
        };

          fetch('/google-pay-response', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload), // Send the token in the payload
            })
                .then(response => {
                    if (!response.ok) {
                       window.location.href = '/google-pay-failed';
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Success:', data);
                    window.location.href = '/google-pay-success/'+ token;
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = '/google-pay-failed';
                    // alert('An error occurred while processing the payment.');
                });
      }
    </script>
  </body>
</html>
