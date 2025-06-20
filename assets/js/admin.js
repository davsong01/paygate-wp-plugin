// function showAlert(type, message) {
//     const alertEl = $('#paygate-alert');
//     const alertMsg = $('#paygate-alert-message');

//     // Remove any previous type classes
//     alertEl.removeClass('d-none notice-success notice-error notice-warning notice-info');
//     console.log('type'+type);
    
//     // Add WordPress-style class
//     switch (type) {
//         case 'success':
//             alertEl.addClass('notice-success');
//             break;
//         case 'error':
//         case 'danger':
//             alertEl.addClass('notice-error');
//             break;
//         case 'warning':
//             alertEl.addClass('notice-warning');
//             break;
//         case 'info':
//             alertEl.addClass('notice-info');
//             break;
//     }

//     // Insert message and show
//     alertMsg.html(message);
//     alertEl.removeClass('d-none');

//     // Scroll to alert
//     $('html, body').animate({ scrollTop: alertEl.offset().top - 100 }, 400);

//     // Auto dismiss after 5 seconds
//     setTimeout(() => {
//         alertEl.fadeOut(300, () => {
//             alertEl.addClass('d-none').removeClass('notice-success notice-error notice-warning notice-info').show();
//         });
//     }, 5000);
// }
