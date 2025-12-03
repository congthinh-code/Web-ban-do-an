-- Bảng loại món ăn
CREATE TABLE Loaimonan (
  Maloai INT AUTO_INCREMENT PRIMARY KEY,
  Tenloai VARCHAR(100) NOT NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng khách hàng + admin + nhân viên (dùng chung)
CREATE TABLE Users (
  UID INT AUTO_INCREMENT PRIMARY KEY,
  Hoten VARCHAR(100) NOT NULL,
  Taikhoan VARCHAR(50) UNIQUE NOT NULL,
  Matkhau VARCHAR(100) NOT NULL,          -- NÊN lưu password_hash, không nên lưu plain text
  Email VARCHAR(100) UNIQUE,
  DienthoaiKH VARCHAR(15),
  DiachiKH VARCHAR(255),
  Ngaysinh DATE,
  Role ENUM('khach','nhanvien','admin')   -- phân quyền
      NOT NULL
      DEFAULT 'khach'
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng món ăn
CREATE TABLE Monan (
  Mamon INT AUTO_INCREMENT PRIMARY KEY,
  Tenmon VARCHAR(100) NOT NULL,
  Giaban DECIMAL(10,2) NOT NULL,
  Giagoc DECIMAL(10,2) NOT NULL,
  Noidung VARCHAR(500),
  Anh VARCHAR(255),
  Maloai INT,
  FOREIGN KEY (Maloai) REFERENCES Loaimonan(Maloai)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng đơn hàng
CREATE TABLE Donhang (
  MaDH INT AUTO_INCREMENT PRIMARY KEY,
  TinhtrangDH VARCHAR(50)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NOT NULL
    DEFAULT 'Đang xử lý',
  Ngaydat DATETIME DEFAULT NOW(),
  Ngaygiao DATETIME NULL,
  UID INT,
  CONSTRAINT fk_donhang_Users
    FOREIGN KEY (UID) REFERENCES Users(UID)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết đơn hàng
CREATE TABLE Chitietdonhang (
  MaCTDH INT AUTO_INCREMENT PRIMARY KEY,
  MaDH INT,
  Mamon INT,
  Soluong INT NOT NULL,
  Dongia DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (MaDH) REFERENCES Donhang(MaDH),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;



-- Bảng giỏ hàng
CREATE TABLE Giohang (
  MaGH INT AUTO_INCREMENT PRIMARY KEY,
  UID INT NOT NULL,
  Mamon INT NOT NULL,
  Soluong INT NOT NULL DEFAULT 1,
  Ngaythem DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_giohang_users
    FOREIGN KEY (UID) REFERENCES Users(UID)
    ON DELETE CASCADE,
  CONSTRAINT fk_giohang_monan
    FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
    ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;




-- Bảng bình luận
CREATE TABLE Binhluan (
  MaBL INT AUTO_INCREMENT PRIMARY KEY,
  UID INT,
  Mamon INT,
  Noidung VARCHAR(1000) NOT NULL,
  Ngaytao DATETIME NOT NULL,
  FOREIGN KEY (UID) REFERENCES Users(UID),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;





/* DỮ LIỆU MẪU */

INSERT INTO Loaimonan (Tenloai) VALUES
('Cơm'), ('Phở'), ('Đồ uống'), ('Bánh ngọt'), ('Mì'), ('Gà rán');

INSERT INTO Monan (Tenmon, Giaban, Giagoc, Noidung, Anh, Maloai) VALUES
('Cơm gà xối mỡ', 45000, 45000, 'Cơm chiên giòn với gà xối mỡ thơm ngon', 'assets/img/comgaxoimo.jpg', 1),
('Phở bò tái', 40000, 40000, 'Phở truyền thống với thịt bò tái mềm', 'assets/img/phobo.jpg', 2),
('Trà sữa trân châu', 30000, 30000, 'Trà sữa vị truyền thống, topping trân châu đen', 'assets/img/trasua.webp', 3),
('Bánh flan caramel', 20000, 20000, 'Bánh flan mềm mịn, sốt caramel thơm ngon', 'assets/img/banhflan.jpg', 4),
('Mì xào hải sản', 55000, 55000, 'Mì xào với tôm, mực, rau củ tươi ngon', 'assets/img/mixaohaisan.jpg', 5),
('Gà rán giòn', 35000, 35000, 'Miếng gà chiên giòn rụm đậm vị', 'assets/img/garan.jpg', 6),
('Nước cam tươi', 25000, 25000, 'Nước cam ép nguyên chất, không đường', 'assets/img/nuoccam.webp', 3),
('Matcha đá xay', 35000, 35000, 'Matcha đá xay', 'assets/img/matchadaxay.webp', 3),
('Nước ép ổi', 25000, 25000, 'Nước ổi ép nguyên chất, không đường', 'assets/img/nuocoi.jpg', 3),
('Cơm chiên dương châu', 50000, 50000, 'Cơm chiên với xúc xích, trứng và rau củ', 'assets/img/comchien.jpg', 1);

-- KHÁCH HÀNG THƯỜNG (role = 'khach')
INSERT INTO Users (Hoten, Taikhoan, Matkhau, Email, DienthoaiKH, DiachiKH, Ngaysinh, Role) VALUES
('Nguyễn Văn A', 'nguyenvana', '123456', 'vana@gmail.com', '0901234567', '123 Lê Lợi, Q1, TP.HCM', '1999-05-15', 'khach'),
('Trần Thị B', 'tranthib', 'abcdef', 'thib@gmail.com', '0912345678', '45 Nguyễn Huệ, TP.HCM', '2000-07-20', 'khach'),
('Lê Văn C', 'levanc', '654321', 'levanc@gmail.com', '0934567890', '22 Hai Bà Trưng, Hà Nội', '1998-12-10', 'khach'),
('Phạm Thị D', 'phamthid', 'dpassword', 'thid@gmail.com', '0923456789', '78 Phan Chu Trinh, Đà Nẵng', '2002-03-05', 'khach');

-- CHUYỂN DỮ LIỆU TỪ BẢNG ADMIN CŨ SANG (role = 'admin' / 'nhanvien')
INSERT INTO Users (Hoten, Taikhoan, Matkhau, Email, DienthoaiKH, DiachiKH, Ngaysinh, Role) VALUES
('Nguyễn Quản Lý', 'admin1', 'admin123', NULL, NULL, NULL, NULL, 'admin'),
('Lê Nhân Viên', 'admin2', 'nhanvien01', NULL, NULL, NULL, NULL, 'nhanvien');

INSERT INTO Donhang (TinhtrangDH, Ngaydat, Ngaygiao, UID) VALUES
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



-- DỮ LIỆU MẪU GIỎ HÀNG

INSERT INTO Giohang (UID, Mamon, Soluong) VALUES
-- User 1: thích cơm gà + trà sữa
(1, 1, 2),   -- 2 phần Cơm gà xối mỡ
(1, 3, 1),   -- 1 ly Trà sữa trân châu

-- User 2: đặt mì xào + nước cam
(2, 5, 1),   -- 1 phần Mì xào hải sản
(2, 7, 2),   -- 2 ly Nước cam tươi

-- User 3: mê gà rán
(3, 6, 3),   -- 3 miếng Gà rán giòn

-- User 4: ăn phở + bánh flan tráng miệng
(4, 2, 1),   -- 1 tô Phở bò tái
(4, 4, 2);   -- 2 bánh flan caramel





INSERT INTO Binhluan (UID, Mamon, Noidung, Ngaytao) VALUES
(1, 1, 'Món này ăn rất ngon, gà giòn và không bị khô.', '2025-11-02 14:20:00'),
(2, 3, 'Trà sữa ngọt vừa, trân châu dẻo ngon.', '2025-11-03 16:00:00'),
(3, 5, 'Mì xào nhiều hải sản, rất chất lượng!', '2025-11-05 09:30:00'),
(4, 6, 'Gà hơi mặn, nhưng vẫn ngon.', '2025-11-06 19:00:00'),
(2, 4, 'Bánh flan mịn, caramel thơm.', '2025-11-07 10:10:00');
