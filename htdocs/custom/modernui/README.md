# ModernUI — giao diện hiện đại cho Dolibarr 24

Module này làm mới toàn bộ giao diện back-office (theme **md** và **eldy**) theo phong cách SaaS: thanh trên màu trắng, sidebar gọn, card bo góc, bảng thoáng, toggle dạng switch, trang đăng nhập 2 cột, font Be Vietnam Pro.

Module chỉ nạp thêm 1 file CSS và 1 file JS. Nó không sửa core, không đổi logic PHP và không đổi class/id HTML. Tắt module là giao diện trở về như cũ.

## Cài đặt

1. Chép thư mục `modernui/` vào `htdocs/custom/`.
2. Kiểm tra `conf.php` đã khai báo thư mục custom (bản cài chuẩn có sẵn):
   ```php
   $dolibarr_main_url_root_alt = '/custom';
   $dolibarr_main_document_root_alt = '.../htdocs/custom';
   ```
3. Vào **Thiết lập > Mô-đun/Ứng dụng**, tìm "ModernUI" (nhóm *Công cụ đa module*) rồi bật.
4. Nhấn `Ctrl + F5` để trình duyệt tải lại CSS.

Muốn gỡ: tắt module, giao diện theme gốc sẽ trở lại ngay.

## Cấu trúc

```
modernui/
├── core/modules/modModernUI.class.php   Descriptor (module_parts css + js)
├── css/modernui.css.php                 Toàn bộ CSS override (đọc ?theme=, bỏ qua khi in)
├── js/modernui.js                       Tinh chỉnh nhỏ, không cần thư viện ngoài
├── fonts/                               Be Vietnam Pro (latin, latin-ext, vietnamese) — chạy offline
└── langs/{vi_VN,en_US}/modernui.lang
```

## Tùy biến

**Màu sắc:** sửa các biến trong khối `:root` ở đầu `css/modernui.css.php` (`--primary`, `--accent`, `--success`…). Các biến gốc của Dolibarr (`--colorbackhmenu1`, `--butactionbg`…) đã được map sang các biến này, nên chỉ cần sửa một chỗ.

**Tên thương hiệu / trang đăng nhập:** sửa object `CONFIG` ở đầu `js/modernui.js` (`brandName`, `heroTitle`, `heroText`, `heroPoints`…). Cách khác là khai báo `window.MODERNUI_CONFIG = {...}` trước khi file JS được nạp. Nếu công ty đã có logo (Thiết lập > Công ty) và logo hiện trên menu, khối thương hiệu sẽ tự dùng logo đó.

**Dark mode (tùy chọn):** thêm `data-mui-theme="dark"` vào thẻ `<html>`. Chế độ này chỉ đổi biến màu nên chưa phủ hết mọi trang, và mặc định không bật.

## Ghi chú kỹ thuật

- Với trang in (`optioncss=print`), file CSS trả về rỗng để bản in giữ nguyên như gốc.
- Không dùng `display: … !important` cho nút, toggle, tab và phân trang. Dolibarr ẩn các phần tử này bằng `style="display:none"`, `.hideobject` hoặc jQuery, nên chúng phải vẫn ẩn được.
- Theme md định vị nút bằng `#mainbody input.button:not(...)` (độ đặc hiệu cao). Các nút secondary/danger dùng selector `#mainbody … :not(.buttongen):not(.bordertransp)` để đè lên.
- Trong eldy, class `sidebar-collapse` luôn có trên `<body>`. Vì vậy rule ẩn sidebar khi thu gọn chỉ áp cho md.
- Nhãn menu trên: dưới 1800px chỉ hiện icon (mục đang chọn vẫn có nhãn), nhãn đầy đủ nằm trong tooltip. Dưới 992px, sidebar chuyển thành menu trượt, mở bằng nút ☰; phím tắt `Ctrl/⌘ + K` mở ô tìm kiếm.
