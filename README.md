# AdminTE - Çok Dilli Yönetim Paneli

Bu proje, çok dilli destek sunan modern bir yönetim panelidir.

## Özellikler

- 🌐 Çoklu dil desteği (Türkçe ve İngilizce)
- 👥 Kullanıcı yönetimi
- 🔒 Rol tabanlı yetkilendirme
- 📱 Responsive tasarım
- 🎨 Modern ve kullanıcı dostu arayüz

## Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Composer
- Web sunucusu (Apache/Nginx)

## Kurulum

1. Projeyi klonlayın:
```bash
git clone https://github.com/kullaniciadi/AdminTE.git
```

2. Composer bağımlılıklarını yükleyin:
```bash
composer install
```

3. Veritabanını oluşturun:
```bash
mysql -u kullanici -p < sql/languages.sql
```

4. `.env.example` dosyasını `.env` olarak kopyalayın ve veritabanı bilgilerinizi girin:
```bash
cp .env.example .env
```

5. Web sunucunuzu projenin kök dizinine yönlendirin.

## Kullanım

1. Tarayıcınızda `http://localhost/AdminTE` adresine gidin
2. Varsayılan giriş bilgileri:
   - Kullanıcı adı: admin
   - Şifre: admin123

## Katkıda Bulunma

1. Bu depoyu fork edin
2. Yeni bir özellik dalı oluşturun (`git checkout -b yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik eklendi'`)
4. Dalınıza push yapın (`git push origin yeni-ozellik`)
5. Bir Pull Request oluşturun

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır. Detaylar için [LICENSE](LICENSE) dosyasına bakın.

## İletişim

- Website: [https://example.com](https://example.com)
- Email: info@example.com
