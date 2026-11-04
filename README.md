# 🐦 MiniX – Mạng xã hội X (Twitter) Clone Full-Stack

> Dự án xây dựng nền tảng mạng xã hội hiện đại lấy cảm hứng 100% từ giao diện và trải nghiệm của **X.com (Twitter)**, tích hợp trợ lý trí tuệ nhân tạo **Grok AI (Google Gemini)**, hệ thống **nhắn tin thời gian thực đa thiết bị**, **cài đặt quyền riêng tư nâng cao** và **bảng điều khiển quản trị viên**.

---

## 📋 Mục lục
1. [Công nghệ sử dụng](#-công-nghệ-sử-dụng)
2. [Các chức năng chính nổi bật](#-các-chức-năng-chính-nổi-bật)
3. [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
4. [Hướng dẫn tải về và cài đặt chi tiết](#-hướng-dẫn-tải-về-và-cài-đặt-chi-tiết)
5. [Hướng dẫn sử dụng trên 2 thiết bị khác nhau (Đa thiết bị / Mạng LAN)](#-hướng-dẫn-sử-dụng-trên-2-thiết-bị-khác-nhau-mạng-lan)
6. [Danh sách tài khoản mẫu (Demo Accounts)](#-danh-sách-tài-khoản-mẫu-demo-accounts)
7. [Kiểm thử tự động (Automated Testing - 89/89 Tests Pass)](#-kiểm-thử-tự-động-automated-testing)
8. [Xử lý sự cố thường gặp (Troubleshooting)](#-xử-lý-sự-cố-thường-gặp-troubleshooting)
9. [Thông tin nhóm thực hiện](#-thông-tin-nhóm-thực-hiện)

---

## 🛠️ Công nghệ sử dụng

- **Backend:** [Laravel 9](https://laravel.com/) (PHP 8.1+) – Kiến trúc MVC, Eloquent ORM, Authentication, Authorization Policies, Storage Disks.
- **Frontend:** [Vue 3](https://vuejs.org/) (`<script setup>` Composition API) – Reactive Components, tối ưu render.
- **Single Page Application (SPA) Adapter:** [Inertia.js](https://inertiajs.com/) – Kết nối mượt mà giữa Laravel và Vue mà không cần viết REST API thủ công, tải trang tức thì không giật lag.
- **Giao diện & Thiết kế:** [Tailwind CSS](https://tailwindcss.com/) – Thiết kế chuẩn X.com, hỗ trợ đầy đủ **Dark Mode** và **Light Mode**.
- **Trí tuệ nhân tạo (AI):** [Google Gemini 3.5 Flash Lite API](https://ai.google.dev/) – Phản hồi thần tốc, hỗ trợ Tiếng Việt và render code Markdown.
- **Cơ sở dữ liệu:** MySQL (MariaDB trong XAMPP).
- **Bộ đóng gói tài nguyên:** [Vite](https://vitejs.dev/) – Tốc độ build siêu nhanh, tối ưu hóa bundle sản phẩm.
- **Bộ icon:** `vue-material-design-icons`.

---

## 🚀 Các chức năng chính nổi bật

### 1. 🤖 Trợ lý AI Thông minh (Grok / Bot)
- **Menu riêng trên Sidebar:** Nút bấm **Bot** chuyên dụng tại thanh điều hướng bên trái.
- **Giao diện chuẩn Grok (X.com):** Trang chat riêng biệt `/bot` với huy hiệu phiên bản AI và đèn báo trạng thái trực tuyến.
- **Tính năng phong phú:**
  - 4 gợi ý câu hỏi nhanh (Prompt Suggestions) khi bắt đầu cuộc trò chuyện.
  - Phân tích và định dạng tin nhắn Markdown: in đậm, danh sách và **khối code lập trình có nút Sao chép mã**.
  - Nút sao chép phản hồi nhanh, hiệu ứng ba chấm động (Typing indicator) khi AI đang phản hồi.
  - Nút **Làm mới đoạn chat** giúp xoá ngữ cảnh và bắt đầu phiên hỏi đáp mới.

### 2. 💬 Nhắn tin trực tiếp 1-1 (Direct Messages - Hoạt động trên 2 thiết bị)
- Trò chuyện riêng tư giữa 2 tài khoản bất kỳ qua giao diện `/messages`.
- **Đồng bộ đa thiết bị tức thì:** Tự động đồng bộ tin nhắn nền mỗi 2.5 giây mà không cần cài đặt dịch vụ Socket phức tạp bên ngoài.
- **Chia sẻ bài viết qua tin nhắn (Tweet DM Share):** Gửi trực tiếp bài viết của người khác vào hộp thư bạn bè kèm lời nhắn.
- **Kiểm soát quyền riêng tư:** Thiết lập cho phép nhận tin nhắn từ *Mọi người* hoặc *Chỉ người bạn đang theo dõi*.

### 3. 📝 Bài viết (Tweets) & Đa phương tiện
- Đăng bài viết tối đa 280 ký tự kèm bộ đếm ký tự thời gian thực.
- Đính kèm hình ảnh (JPG, PNG, GIF, WebP lên đến 5MB) và video (MP4, MOV lên đến 50MB).
- Tích hợp sẵn bộ chọn **Emoji** và chèn ảnh động **GIF**.
- Hệ thống phản hồi phân cấp theo chuỗi hội thoại (Threaded Replies) tại đường dẫn `/{username}/status/{id}`.
- Trích dẫn bài viết (Quote Tweet) kèm khung xem trước trực quan.
- Xóa bài viết an toàn với cơ chế Observer tự động dọn sạch file đa phương tiện vật lý khỏi ổ cứng để tránh rác dung lượng.

### 4. ❤️ Tương tác xã hội & Số liệu
- Tương tác phản hồi tức thì (Optimistic UI): Thích (Like), Đăng lại (Retweet), Đánh dấu (Bookmark) với hiệu ứng đổi trạng thái ngay lập tức.
- Bộ sưu tập đánh dấu cá nhân riêng tư tại `/bookmarks`.
- Bộ đếm lượt xem (Views counter) áp dụng cơ chế lọc chống gian lận (Deduplication) theo phiên truy cập.

### 5. 👥 Mạng lưới kết nối (Follow, Block & Mute)
- Theo dõi / Hủy theo dõi người dùng kèm danh sách người theo dõi (`/{username}/followers`) và đang theo dõi (`/{username}/following`).
- **Chặn (Block):** Ngắt ngay lập tức quan hệ theo dõi 2 chiều, ẩn hoàn toàn bài viết của nhau khỏi dòng thời gian.
- **Tắt tiếng (Mute):** Ẩn bài viết của người bị tắt tiếng mà không làm gián đoạn danh sách theo dõi.

### 6. 🔍 Khám phá & Tìm kiếm (Explore & Search)
- Tìm kiếm toàn diện theo từ khóa bài viết hoặc tên tài khoản tại `/search`.
- Trang khám phá `/explore` hiển thị các chủ đề và hashtag xu hướng được tổng hợp theo thời gian thực trong 7 ngày gần nhất.

### 7. 🔔 Trung tâm thông báo (Notifications)
- Nhận thông báo tức thì khi có người thích, đăng lại, bình luận bài viết hoặc theo dõi bạn.
- Huy hiệu đỏ hiển thị số lượng thông báo chưa đọc trên menu.
- Chức năng "Đánh dấu tất cả là đã đọc".

### 8. ⚙️ Cài đặt & Quyền riêng tư (Settings - Chuẩn 4 mục theo X.com)
- **Tài khoản của bạn (Your Account):** Xem thông tin tài khoản, đổi mật khẩu bảo mật, tải bản lưu trữ dữ liệu tài khoản (Export archive JSON), vô hiệu hóa tài khoản (Deactivate).
- **Quyền riêng tư và an toàn (Privacy and safety):** Tùy chỉnh quyền nhận tin nhắn riêng tư, quản lý danh sách tài khoản đã chặn và đã tắt tiếng.
- **Thông báo (Notifications):** Bật/tắt tùy chọn nhận thông báo.
- **Creator Studio:** Bảng phân tích số liệu đăng bài, tổng lượt hiển thị và tỷ lệ tương tác.

### 9. 🛡️ Bảng điều khiển Quản trị viên (Admin Dashboard)
- Dành riêng cho tài khoản có quyền Admin (`/admin`).
- Thống kê toàn hệ thống: tổng người dùng, tổng bài viết, tổng tương tác và số lượng báo cáo vi phạm.
- Quản lý người dùng: khóa tài khoản vi phạm (Khóa tài khoản sẽ chặn đăng bài/tương tác), thăng cấp hoặc hạ quyền Admin.
- Xử lý báo cáo vi phạm: xem bài viết bị báo cáo, xóa bài viết vi phạm, đánh dấu đã giải quyết.

---

## 💻 Yêu cầu hệ thống

Trước khi cài đặt, máy tính của bạn cần có:
1. **XAMPP** (hoặc môi trường chạy PHP >= 8.1 và MySQL).
2. **Composer** (Công cụ quản lý gói PHP – [Tải tại getcomposer.org](https://getcomposer.org/)).
3. **Node.js** (Phiên bản LTS 18.x hoặc 20.x – [Tải tại nodejs.org](https://nodejs.org/)) & **NPM**.
4. **Git** (Dùng để clone mã nguồn – [Tải tại git-scm.com](https://git-scm.com/)).

---

## 📥 Hướng dẫn tải về và cài đặt chi tiết

### Bước 1: Tải mã nguồn về máy

Mở **Terminal** (hoặc **PowerShell** / **Git Bash**) và chạy lệnh:

```bash
git clone https://github.com/thanhbinh-io/MiniX.git
cd MiniX
```

*(Hoặc nếu bạn tải file `.zip`, hãy giải nén vào thư mục, ví dụ: `C:\xampp\htdocs\twitter-clone` và mở terminal tại thư mục đó).*

---

### Bước 2: Cài đặt các thư viện Backend (Composer)

Chạy lệnh để tải các gói phụ thuộc PHP:

```bash
composer install
```

---

### Bước 3: Cài đặt các thư viện Frontend (NPM)

Chạy lệnh cài đặt các gói Vue, Tailwind CSS, Inertia:

```bash
npm install
```

---

### Bước 4: Tạo file cấu hình môi trường (.env)

Sao chép file mẫu `.env.example` thành `.env`:

- **Trên Windows (PowerShell):**
  ```powershell
  copy .env.example .env
  ```
- **Trên Linux / macOS:**
  ```bash
  cp .env.example .env
  ```

Sau đó tạo khóa bảo mật cho ứng dụng:

```bash
php artisan key:generate
```

---

### Bước 5: Cấu hình Cơ sở dữ liệu và API Key Gemini

Mở ứng dụng **XAMPP Control Panel**, nhấn **Start** tại 2 dịch vụ **Apache** và **MySQL**.

Mở file `.env` bằng trình soạn thảo (VS Code, Notepad,...) và kiểm tra các thông số:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=twitter-clone
DB_USERNAME=root
DB_PASSWORD=

# Khóa kết nối Chatbot Grok AI (Google Gemini API)
GEMINI_API_KEY=your_gemini_api_key_here
GEMINI_MODEL=gemini-3.5-flash-lite
```

> **Lưu ý:** Mở trình duyệt vào `http://localhost/phpmyadmin`, tạo một cơ sở dữ liệu mới có tên đúng với `DB_DATABASE` (ví dụ: `twitter-clone`) với bảng mã `utf8mb4_unicode_ci`.

---

### Bước 6: Tạo liên kết thư mục lưu trữ ảnh/video

Để ảnh đại diện, ảnh bìa và video tải lên hiển thị được trên web, chạy lệnh:

```bash
php artisan storage:link
```

---

### Bước 7: Khởi tạo bảng dữ liệu và nạp dữ liệu mẫu

Chạy migration và seeder để tạo sẵn bài viết, bình luận, người dùng mẫu và tài khoản quản trị viên:

```bash
php artisan migrate --seed
```

---

### Bước 8: Biên dịch giao diện Frontend (Production Build)

Để website hoạt động ổn định nhất và không bị phụ thuộc vào máy chủ Vite Dev, hãy chạy lệnh build một lần duy nhất:

```bash
npm run build
```

> **Mẹo:** Sau khi chạy lệnh này, toàn bộ CSS và JavaScript đã được tối ưu sẵn trong thư mục `public/build/`.

---

### Bước 9: Khởi chạy máy chủ ứng dụng

Khởi động server Laravel:

```bash
php artisan serve
```

Mở trình duyệt truy cập: **`http://localhost:8000`** (hoặc `http://127.0.0.1:8000`).

---

## 📱 Hướng dẫn sử dụng trên 2 thiết bị khác nhau (Mạng LAN)

> **Yêu cầu giáo viên:** Website có thể chạy và chat qua lại giữa 2 thiết bị khác nhau (ví dụ: Máy tính và Điện thoại, hoặc 2 máy tính kết nối chung mạng Wi-Fi).

### Bước 1: Tìm địa chỉ IP LAN của máy tính làm máy chủ

Mở PowerShell / Command Prompt trên máy chủ và gõ:
```powershell
ipconfig
```
Tìm dòng **IPv4 Address** của Wi-Fi hoặc Ethernet đang kết nối (ví dụ: `192.168.1.15` hoặc `192.168.33.103`).

### Bước 2: Chạy server lắng nghe toàn mạng

Chạy lệnh với tham số `--host=0.0.0.0`:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Bước 3: Kết nối từ thiết bị thứ 2

1. Đảm bảo cả 2 thiết bị cùng kết nối vào **chung một mạng Wi-Fi**.
2. Trên thiết bị 1 (Máy tính): Mở trình duyệt vào `http://localhost:8000`, đăng nhập bằng tài khoản **User A** (ví dụ: `bin@gmail.com`).
3. Trên thiết bị 2 (Điện thoại hoặc laptop khác): Mở trình duyệt vào `http://<IP_LAN_CỦA_BẠN>:8000` (ví dụ: `http://192.168.1.15:8000`), đăng nhập bằng tài khoản **User B** (ví dụ: `demo@example.com`).
4. Vào mục **Chat (Tin nhắn)**, chọn người nhận và nhắn tin qua lại. Tin nhắn mới sẽ tự động hiển thị trên cả 2 màn hình sau mỗi 2.5 giây mà không cần bấm F5!

---

## 👤 Danh sách tài khoản mẫu (Demo Accounts)

Hệ thống đã nạp sẵn dữ liệu kiểm thử. Tất cả tài khoản đều dùng chung mật khẩu: **`password`**

| Vai trò | Tên hiển thị | Tên tài khoản | Email đăng nhập | Mật khẩu |
|---|---|---|---|---|
| 👑 **Admin Quản trị** | Demo User | `@demouser` | `demo@example.com` | `password` |
| 👑 **Admin Quản trị** | Binh Admin | `@bin` | `bin@gmail.com` | `password` |
| 🧑 **Người dùng mẫu** | Taylor Otwell | `@taylorotwell` | `taylor@laravel.com` | `password` |
| 🧑 **Người dùng mẫu** | Evan You | `@youyuxi` | `evan@vuejs.org` | `password` |
| 🧑 **Người dùng mẫu** | Elon Musk | `@elonmusk` | `elon@x.com` | `password` |
| 🧑 **Người dùng mẫu** | John Weeks | `@johnweeksdev` | `john@example.com` | `password` |

---

## 🧪 Kiểm thử tự động (Automated Testing)

Dự án được viết bộ kiểm thử tự động toàn diện với **89 Test Cases** bao phủ toàn bộ các chức năng từ xác thực, phân quyền, bài viết, tin nhắn, quyền riêng tư đến Chatbot AI.

Chạy lệnh kiểm thử:

```bash
php artisan test
```

**Kết quả kiểm thử đạt chuẩn:**
```text
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\Auth\AuthenticationTest
   PASS  Tests\Feature\Auth\EmailVerificationTest
   PASS  Tests\Feature\Auth\PasswordConfirmationTest
   PASS  Tests\Feature\Auth\PasswordResetTest
   PASS  Tests\Feature\Auth\PasswordUpdateTest
   PASS  Tests\Feature\Auth\RegistrationTest
   PASS  Tests\Feature\BotTest (6 tests)
   PASS  Tests\Feature\DirectMessageShareTest (5 tests)
   PASS  Tests\Feature\ExampleTest
   PASS  Tests\Feature\InteractionTest (8 tests)
   PASS  Tests\Feature\NotificationTest (6 tests)
   PASS  Tests\Feature\ProfileAndFollowTest (8 tests)
   PASS  Tests\Feature\ProfileTest (5 tests)
   PASS  Tests\Feature\SearchAndExploreTest (4 tests)
   PASS  Tests\Feature\SettingsTest (7 tests)
   PASS  Tests\Feature\TimelineAndStorageTest (8 tests)
   PASS  Tests\Feature\TweetTest (7 tests)

   Tests:  89 passed (100%)
```

---

## ❓ Xử lý sự cố thường gặp (Troubleshooting)

### 1. Trình duyệt hiển thị màn hình trắng
- **Nguyên nhân:** File tạm `public/hot` còn sót lại đang cố nạp file từ cổng Vite Dev (`5173`) thay vì bản build.
- **Cách xử lý:**
  1. Kiểm tra và xóa file `public/hot` nếu có:
     ```powershell
     Remove-Item -Force public\hot
     ```
  2. Chạy lại lệnh build:
     ```bash
     npm run build
     ```
  3. Nhấn **`Ctrl + F5`** (hoặc `Ctrl + Shift + R`) trên trình duyệt để xóa sạch cache.

### 2. Không kết nối được cơ sở dữ liệu (`SQLSTATE[HY000] [2002]`)
- Kiểm tra xem MySQL trong bảng điều khiển **XAMPP Control Panel** đã được bật (màu xanh lá) hay chưa.
- Kiểm tra cổng MySQL mặc định là `3306` trong file `.env`.

### 3. Ảnh bài viết hoặc avatar không hiển thị
- Chạy lại lệnh tạo liên kết thư mục:
  ```bash
  php artisan storage:link
  ```

---

## 👥 Thông tin nhóm thực hiện

- **Đơn vị:** Dự án môn học Phát triển ứng dụng Web
- **Tên dự án:** Mạng xã hội MiniX (Twitter / X Clone)
- **Nhóm thực hiện:** **Nhóm 9**

---

*Chúc các bạn và thầy cô có trải nghiệm mượt mà với dự án MiniX!*
