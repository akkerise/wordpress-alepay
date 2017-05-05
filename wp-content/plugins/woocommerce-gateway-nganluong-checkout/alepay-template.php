<!-- Latest compiled and minified CSS & JS -->
<link rel="stylesheet" media="screen" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://unpkg.com/vue"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<div class="card-alepay" style="width: 20rem;">
    <div class="card-block">
        <h4 class="card-title center">AkKe Inc.</h4>
        <input type="text" class="alepay-input" id="buyer-name" placeholder="Họ và tên">
        <input type="text" class="alepay-input" id="buyer-number" placeholder="Số thẻ">
        <input type="text" class="alepay-input" id="buy-end-day" placeholder="Ngày hết hạn">
        <input type="text" class="alepay-input" id="cvc-card" placeholder="CVV" maxlength="3">
        <input type="email" class="alepay-input" id="buyer-email" name="email" placeholder="Email">
        <input type="text" class="alepay-input" id="phone-number" placeholder="Số điện thoại di động">
        <input type="checkbox" id="buyer-checkbox">
        <p>Nhớ để thanh toán lần sau</p>
    </div>
</div>
<style>
    div.card-alepay {
        font-size: 1.2em;
        font-family: "Helvetica Neue", sans-serif;
        border-radius: 5px;
        width: 500px;
    }

    h4 {
        letter-spacing: 0.15em;
        font-weight: 800;
    }

    input {
        width: 100%;
    }
</style>
<script>
    $(document).ready(function () {
        let alepayInput = $('.alepay-input');
        let buyerName = $('#buyer-name').val();
        let buyerNuber = $('#buyer-number');
        let buyEndDay = $('#buy-end-day');
        let buyerEmail = $('#buyer-email');
        let phoneNumber = $('#phone-number');
        let cvcCard = $('#cvc-card');
        buyerName.keyup(function(){
            console.log(buyerName.val());
        });
    });
</script>