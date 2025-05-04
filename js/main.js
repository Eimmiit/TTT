document.addEventListener('DOMContentLoaded', function() {
  // Donation Modal Elements
  const donationOverlay = document.getElementById('donationOverlay');
  const donateBtn = document.getElementById('donateBtn');
  const donateBtnMobile = document.getElementById('donateBtnMobile');
  const closeDonation = document.getElementById('closeDonation');
  const tabBtns = document.querySelectorAll('.tab-btn');
  const amountBtns = document.querySelectorAll('.amount-btn');
  const customAmount = document.getElementById('customAmount');
  const dedicateDonation = document.getElementById('dedicateDonation');
  const dedicationFields = document.getElementById('dedicationFields');
  const submitDonation = document.getElementById('submitDonation');

  // Donation Data State
  let donationData = {
      type: 'one-time',
      amount: 0,
      designation: 'general',
      dedication: null,
      comment: ''
  };

   // Show donation modal
   function showDonationModal() {
    donationOverlay.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
  
  // Hide donation modal
  function hideDonationModal() {
    donationOverlay.classList.add('hidden');
    document.body.style.overflow = '';
  }
  

  // Tab Handling
  tabBtns.forEach(btn => {
      btn.addEventListener('click', function() {
          tabBtns.forEach(tab => {
              tab.classList.remove('active', 'border-b-2', 'border-primary-600', 'text-neutral-700');
              tab.classList.add('text-neutral-500');
          });
          
          this.classList.add('active', 'border-b-2', 'border-primary-600', 'text-neutral-700');
          this.classList.remove('text-neutral-500');
          
          const tabId = this.dataset.tab;
          document.querySelectorAll('.donation-tab').forEach(tab => tab.classList.add('hidden'));
          document.getElementById(`${tabId}-tab`).classList.remove('hidden');
          
          donationData.type = tabId;
          updatePayPalButton();
      });
  });

  // Amount Selection Handling
  amountBtns.forEach(btn => {
      btn.addEventListener('click', function() {
          amountBtns.forEach(b => b.classList.remove('bg-primary-100', 'border-primary-600'));
          this.classList.add('bg-primary-100', 'border-primary-600');
          
          donationData.amount = parseFloat(this.dataset.amount);
          customAmount.value = '';
          updatePayPalButton();
      });
  });

  // Custom Amount Handling
  customAmount.addEventListener('input', function(e) {
      const value = parseFloat(e.target.value);
      if (!isNaN(value)) {
          amountBtns.forEach(b => b.classList.remove('bg-primary-100', 'border-primary-600'));
          donationData.amount = value;
          updatePayPalButton();
      }
  });

  // Dedication Handling
  dedicateDonation.addEventListener('change', function() {
      dedicationFields.classList.toggle('hidden', !this.checked);
      if (!this.checked) donationData.dedication = null;
  });

  // PayPal Integration
  let paypalButtonInstance = null;

  function updatePayPalButton() {
      // Validate amount before rendering
      if (donationData.amount <= 0 || isNaN(donationData.amount)) {
          return;
      }

      // Clear existing button
      if (paypalButtonInstance) {
          paypalButtonInstance.close();
          paypalButtonInstance = null;
      }

      // Render new button
      paypalButtonInstance = paypal.Buttons({
          style: {
              layout: 'vertical',
              color: 'blue',
              shape: 'rect',
              label: 'paypal'
          },
          createOrder: (data, actions) => createPayPalOrder(),
          onApprove: (data, actions) => handlePaymentApproval(data),
          onError: err => handlePaymentError(err)
      }).render('#paypal-button-container');
  }

  async function createPayPalOrder() {
      try {
          const response = await fetch('create_donation.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({
                  amount: donationData.amount,
                  type: donationData.type,
                  designation: document.getElementById('donationDesignation').value
              })
          });
          
          const data = await response.json();
          if (!response.ok) throw new Error(data.error);
          return data.orderID;
          
      } catch (error) {
          console.error('Order creation failed:', error);
          alert('Failed to create payment order. Please try again.');
      }
  }

  async function handlePaymentApproval(data) {
      try {
          // Capture payment
          const captureResponse = await fetch(`capture_donation.php?orderID=${data.orderID}`);
          const captureData = await captureResponse.json();
          
          if (!captureResponse.ok) throw new Error(captureData.error);

          // Save donation details
          await saveDonationDetails({
              ...donationData,
              transactionId: captureData.id,
              payerEmail: captureData.payer.email_address
          });

          // Show success and reset
          hideDonationModal();
          showSuccessMessage(captureData);

      } catch (error) {
          console.error('Payment capture failed:', error);
          alert('Payment processing failed. Please contact support.');
      }
  }

  function handlePaymentError(err) {
      console.error('PayPal Error:', err);
      alert('Payment system error. Please try another method.');
  }

  async function saveDonationDetails(donationDetails) {
      // Add validation for dedication info
      if (dedicateDonation.checked) {
          donationDetails.dedication = {
              type: document.getElementById('dedicationType').value,
              name: document.getElementById('dedicationName').value || 'Anonymous'
          };
      }

      donationDetails.comment = document.getElementById('donationComment').value;

      try {
          await fetch('/save-donation.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify(donationDetails)
          });
      } catch (error) {
          console.error('Failed to save donation:', error);
      }
  }

  function showSuccessMessage(details) {
      const message = `Thank you for your $${details.amount} donation!\n
                     Transaction ID: ${details.id}\n
                     A receipt has been sent to ${details.payer.email_address}`;
      alert(message);
  }

  // Event Listeners
  if (donateBtn) donateBtn.addEventListener('click', showDonationModal);
  if (donateBtnMobile) donateBtnMobile.addEventListener('click', showDonationModal);
  if (closeDonation) closeDonation.addEventListener('click', hideDonationModal);
  
  donationOverlay.addEventListener('click', e => {
      if (e.target === donationOverlay) hideDonationModal();
  });

  // Form Validation
  submitDonation.addEventListener('click', function(e) {
      e.preventDefault();
      
      if (donationData.amount <= 0 || isNaN(donationData.amount)) {
          alert('Please select or enter a valid donation amount.');
          return;
      }

      if (dedicateDonation.checked && !document.getElementById('dedicationName').value) {
          alert('Please enter a name for your dedication.');
          return;
      }

      updatePayPalButton();

      // Trigger PayPal button click programmatically to start payment
      const paypalBtn = document.querySelector('#paypal-button-container iframe');
      if (paypalBtn) {
          paypalBtn.contentWindow.focus();
          paypalBtn.contentWindow.document.querySelector('button').click();
      }
  });
});