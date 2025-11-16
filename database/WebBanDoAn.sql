

USE WebBanDoAn;

CREATE TABLE Loaimonan (
  Maloai INT AUTO_INCREMENT PRIMARY KEY,
  Tenloai VARCHAR(100) NOT NULL
);

CREATE TABLE Monan (
  Mamon INT AUTO_INCREMENT PRIMARY KEY,
  Tenmon VARCHAR(100) NOT NULL,
  Giaban DECIMAL(10,2) NOT NULL,
  Noidung VARCHAR(500),
  Anh VARCHAR(255),
  Maloai INT,
  FOREIGN KEY (Maloai) REFERENCES Loaimonan(Maloai)
);

CREATE TABLE Khachhang (
  MaKH INT AUTO_INCREMENT PRIMARY KEY,
  Hoten VARCHAR(100) NOT NULL,
  Taikhoan VARCHAR(50) UNIQUE NOT NULL,
  Matkhau VARCHAR(100) NOT NULL,
  Email VARCHAR(100) UNIQUE,
  DienthoaiKH VARCHAR(15),
  DiachiKH VARCHAR(255),
  Ngaysinh DATE
);

CREATE TABLE Donhang (
  MaDH INT AUTO_INCREMENT PRIMARY KEY,
  TinhtrangDH VARCHAR(50) DEFAULT 'Đang xử lý',
  Ngaydat DATETIME DEFAULT NOW(),
  Ngaygiao DATETIME NULL,
  MaKH INT,
  FOREIGN KEY (MaKH) REFERENCES Khachhang(MaKH)
);

CREATE TABLE Chitietdonhang (
  MaCTDH INT AUTO_INCREMENT PRIMARY KEY,
  MaDH INT,
  Mamon INT,
  Soluong INT NOT NULL,
  Dongia DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (MaDH) REFERENCES Donhang(MaDH),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
);

CREATE TABLE Binhluan (
  MaBL INT AUTO_INCREMENT PRIMARY KEY,
  MaKH INT,
  Mamon INT,
  Noidung VARCHAR(1000) NOT NULL,
  Ngaytao DATETIME NOT NULL,
  FOREIGN KEY (MaKH) REFERENCES Khachhang(MaKH),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
);

CREATE TABLE Admin (
  UserAdmin VARCHAR(50) PRIMARY KEY,
  PassAdmin VARCHAR(100) NOT NULL,
  Hoten VARCHAR(100) NOT NULL,
  Gioitinh VARCHAR(50),
  Namsinh INT,
  Quyenhan VARCHAR(50) DEFAULT 'Nhân viên'
);

INSERT INTO Loaimonan (Tenloai) VALUES
('Cơm'), ('Phở'), ('Đồ uống'), ('Bánh ngọt'), ('Mì'), ('Gà rán');

INSERT INTO Monan (Tenmon, Giaban, Noidung, Anh, Maloai) VALUES
('Cơm gà xối mỡ', 45000, 'Cơm chiên giòn với gà xối mỡ thơm ngon', 'image/comgaxoimo.jpg', 1),
('Phở bò tái', 40000, 'Phở truyền thống với thịt bò tái mềm', 'image/phobo.jpg', 2),
('Trà sữa trân châu', 30000, 'Trà sữa vị truyền thống, topping trân châu đen', 'image/trasua.jpg', 3),
('Bánh flan caramel', 20000, 'Bánh flan mềm mịn, sốt caramel thơm ngon', 'image/banhflan.jpg', 4),
('Mì xào hải sản', 55000, 'Mì xào với tôm, mực, rau củ tươi ngon', 'image/mixaohaison.jpg', 5),
('Gà rán giòn', 35000, 'Miếng gà chiên giòn rụm đậm vị', 'image/garan.jpg', 6),
('Nước cam tươi', 25000, 'Nước cam ép nguyên chất, không đường', 'image/nuoccam.jpg', 3),
('Cơm chiên dương châu', 50000, 'Cơm chiên với xúc xích, trứng và rau củ', 'image/comchien.jpg', 1);

INSERT INTO Khachhang (Hoten, Taikhoan, Matkhau, Email, DienthoaiKH, DiachiKH, Ngaysinh) VALUES
('Nguyễn Văn A', 'nguyenvana', '123456', 'vana@gmail.com', '0901234567', '123 Lê Lợi, Q1, TP.HCM', '1999-05-15'),
('Trần Thị B', 'tranthib', 'abcdef', 'thib@gmail.com', '0912345678', '45 Nguyễn Huệ, TP.HCM', '2000-07-20'),
('Lê Văn C', 'levanc', '654321', 'levanc@gmail.com', '0934567890', '22 Hai Bà Trưng, Hà Nội', '1998-12-10'),
('Phạm Thị D', 'phamthid', 'dpassword', 'thid@gmail.com', '0923456789', '78 Phan Chu Trinh, Đà Nẵng', '2002-03-05');

INSERT INTO Donhang (TinhtrangDH, Ngaydat, Ngaygiao, MaKH) VALUES
('Đang xử lý', '2025-11-01 10:30:00', NULL, 1),
('Đã giao', '2025-11-02 12:45:00', '2025-11-03 14:00:00', 2),
('Đang giao', '2025-11-05 09:15:00', NULL, 3),
('Đã hủy', '2025-11-06 18:00:00', NULL, 4);

INSERT INTO Chitietdonhang (MaDH, Mamon, Soluong, Dongia) VALUES
(1, 1, 2, 45000),
(1, 3, 1, 30000),
(2, 5, 1, 55000),
(2, 7, 2, 25000),
(3, 6, 3, 35000),
(4, 2, 1, 40000);

INSERT INTO Binhluan (MaKH, Mamon, Noidung, Ngaytao) VALUES
(1, 1, 'Món này ăn rất ngon, gà giòn và không bị khô.', '2025-11-02 14:20:00'),
(2, 3, 'Trà sữa ngọt vừa, trân châu dẻo ngon.', '2025-11-03 16:00:00'),
(3, 5, 'Mì xào nhiều hải sản, rất chất lượng!', '2025-11-05 09:30:00'),
(4, 6, 'Gà hơi mặn, nhưng vẫn ngon.', '2025-11-06 19:00:00'),
(2, 4, 'Bánh flan mịn, caramel thơm.', '2025-11-07 10:10:00');

INSERT INTO Admin (UserAdmin, PassAdmin, Hoten, Gioitinh, Namsinh, Quyenhan) VALUES
('admin1', 'admin123', 'Nguyễn Quản Lý', 'Nam', 1990, 'Quản lý'),
('admin2', 'nhanvien01', 'Lê Nhân Viên', 'Nữ', 1995, 'Nhân viên');
