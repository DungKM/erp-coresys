# Seeder dữ liệu demo

`seed_demo_data.php` nạp dữ liệu mẫu tiếng Việt vào một database **trống** để chạy demo. Dữ liệu mô phỏng một công ty phân phối thiết bị / vật tư công nghiệp.

Script đi qua đúng các lớp nghiệp vụ của Dolibarr (`create`, `addline`, `validate`, `Paiement`…), nên số chứng từ, tổng tiền, VAT, công nợ và bút toán ngân hàng đều khớp như khi nhập tay.

## Chạy

```bash
# PHP CLI của Laragon
D:/projects/laragon/bin/php/php-8.3.0-nts-Win32-vs16-x64/php.exe scripts/demo/seed_demo_data.php --scale=2
```

| Tùy chọn | Ý nghĩa |
|---|---|
| `--scale=N` | Nhân khối lượng dữ liệu (mặc định 1). `--scale=2`: khoảng 240 bên thứ ba, 350 hóa đơn, 240 báo giá. |
| `--login=admin` | Tài khoản admin dùng để tạo dữ liệu. |
| `--no-company` | Không khai báo thông tin công ty, quốc gia Việt Nam và tiền tệ VND. |
| `--force` | Vẫn chạy khi DB đã có dữ liệu demo (dữ liệu sẽ bị nhân đôi). |

**Hãy sao lưu DB trước khi chạy.** Script không có chức năng xóa dữ liệu. Muốn quay lại thì restore bản sao lưu:

```bash
mysqldump -uroot -p dolibarr > documents/admin/backup/before_demoseed.sql   # trước khi seed
mysql -uroot -p dolibarr < documents/admin/backup/before_demoseed.sql       # để quay lại
```

## Dữ liệu được tạo (`--scale=2`)

- **Công ty:** tên, địa chỉ, quốc gia Việt Nam. Tiền tệ đổi sang VND, 0 chữ số thập phân (chỉ đổi khi chưa có hóa đơn nào).
- **Người dùng / HRM:** 64 nhân viên chia 6 phòng ban, có quản lý trực tiếp. Kèm hồ sơ công việc, vị trí, kỹ năng, 140 đơn nghỉ phép (nháp / chờ duyệt / đã duyệt), 6 vị trí tuyển dụng và khoảng 90 hồ sơ ứng viên. Mật khẩu chung của nhân viên demo là `Demo@2026`.
- **Bên thứ ba:** 180 khách hàng, 60 khách hàng tiềm năng, 60 nhà cung cấp, hơn 600 liên hệ. Có mã số thuế, hạn mức công nợ và nhân viên kinh doanh phụ trách.
- **Sản phẩm:** 100 sản phẩm công nghiệp (vòng bi, van, biến tần, PLC…) và 10 dịch vụ. VAT 5% / 8% / 10%.
- **Thương mại:**
  - 240 báo giá: nháp, đã xác nhận, đã ký, không ký.
  - 196 đơn hàng bán: tạo từ báo giá đã ký hoặc tạo độc lập; có đơn đang giao, có đơn đã giao.
  - 50 hợp đồng dịch vụ, 60 phiếu can thiệp.
- **Hóa đơn và thanh toán:** 352 hóa đơn trong 12 tháng gần nhất. Khoảng 65% đã thanh toán đủ, một phần thanh toán dở, còn lại chưa trả (nhiều hóa đơn đã quá hạn, dùng để demo kiểm soát công nợ). 294 phiếu thu được ghi vào ngân hàng.
- **Ngân hàng:** 5 tài khoản (VCB, Techcombank, ACB, VietinBank, quỹ tiền mặt) và khoảng 180 giao dịch thu/chi khác (lương, thuê văn phòng, thuế…). Số dư cuối kỳ luôn dương.
- **Khác:** 120 ticket khiếu nại/yêu cầu, 160 thành viên (3 loại, có đóng phí), 30 khoản tài trợ.

Dữ liệu ngẫu nhiên nhưng cố định (seed `20260924`), nên chạy lại trên DB trống sẽ ra đúng cùng một bộ dữ liệu. Bên thứ ba, sản phẩm, người dùng và liên hệ được gắn `import_key = 'DEMOSEED'`.
