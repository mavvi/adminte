<?php
$subject = "Şifre Sıfırlama İsteği";
include 'header.php';
?>

<h2>Merhaba <?php echo $user_name; ?>,</h2>

<p>Şifrenizi sıfırlamak için aşağıdaki butona tıklayın:</p>

<div style="text-align: center;">
    <a href="<?php echo $reset_link; ?>" class="button">Şifremi Sıfırla</a>
</div>

<p>Bu bağlantı 1 saat süreyle geçerlidir.</p>

<p>Eğer bu isteği siz yapmadıysanız, bu e-postayı görmezden gelebilirsiniz.</p>

<?php include 'footer.php'; ?> 