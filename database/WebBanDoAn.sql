/* --------------------------------------------- */
/*   TẠO DATABASE (nếu cần)                      */
/* --------------------------------------------- */
CREATE DATABASE IF NOT EXISTS webbandoan
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
USE webbandoan;


/* --------------------------------------------- */
/*   BẢNG LOẠI MÓN ĂN                            */
/* --------------------------------------------- */
CREATE TABLE Loaimonan (
  Maloai INT AUTO_INCREMENT PRIMARY KEY,
  Tenloai VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* --------------------------------------------- */
/*   BẢNG USERS                                  */
/* --------------------------------------------- */
CREATE TABLE Users (
  UID INT AUTO_INCREMENT PRIMARY KEY,
  Hoten VARCHAR(100) NOT NULL,
  Taikhoan VARCHAR(50) UNIQUE NOT NULL,
  Matkhau VARCHAR(100) NOT NULL,
  Email VARCHAR(100) UNIQUE,
  DienthoaiKH VARCHAR(15),
  DiachiKH VARCHAR(255),
  Ngaysinh DATE,
  Role ENUM('khach','nhanvien','admin') NOT NULL DEFAULT 'khach'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* --------------------------------------------- */
/*   BẢNG MÓN ĂN                                 */
/* --------------------------------------------- */
CREATE TABLE Monan (
  Mamon INT AUTO_INCREMENT PRIMARY KEY,
  Tenmon VARCHAR(100) NOT NULL,
  Giaban DECIMAL(10,2) NOT NULL,
  Giagoc DECIMAL(10,2) NOT NULL,
  Noidung VARCHAR(500),
  Anh VARCHAR(255),
  Maloai INT,
  FOREIGN KEY (Maloai) REFERENCES Loaimonan(Maloai)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* --------------------------------------------- */
/*   BẢNG ĐƠN HÀNG                               */
/* --------------------------------------------- */
CREATE TABLE Donhang (
  MaDH INT AUTO_INCREMENT PRIMARY KEY,
  TinhtrangDH VARCHAR(50) NOT NULL DEFAULT 'Đang xử lý',
  Ngaydat DATETIME DEFAULT NOW(),
  Ngaygiao DATETIME NULL,
  UID INT,
  FOREIGN KEY (UID) REFERENCES Users(UID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* --------------------------------------------- */
/*   BẢNG CHI TIẾT ĐƠN HÀNG                      */
/* --------------------------------------------- */
CREATE TABLE Chitietdonhang (
  MaCTDH INT AUTO_INCREMENT PRIMARY KEY,
  MaDH INT,
  Mamon INT,
  Soluong INT NOT NULL,
  Dongia DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (MaDH) REFERENCES Donhang(MaDH),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* --------------------------------------------- */
/*   BẢNG GIỎ HÀNG                               */
/* --------------------------------------------- */
CREATE TABLE Giohang (
  MaGH INT AUTO_INCREMENT PRIMARY KEY,
  UID INT NOT NULL,
  Mamon INT NOT NULL,
  Soluong INT NOT NULL DEFAULT 1,
  Ngaythem DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (UID) REFERENCES Users(UID) ON DELETE CASCADE,
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/* --------------------------------------------- */
/*   BẢNG BÌNH LUẬN                              */
/* --------------------------------------------- */
CREATE TABLE Binhluan (
  MaBL INT AUTO_INCREMENT PRIMARY KEY,
  UID INT,
  Mamon INT,
  Noidung VARCHAR(1000) NOT NULL,
  Ngaytao DATETIME NOT NULL,
  FOREIGN KEY (UID) REFERENCES Users(UID),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



/* --------------------------------------------- */
/*   DỮ LIỆU MẪU LOẠI MÓN ĂN                    */
/* --------------------------------------------- */
INSERT INTO Loaimonan (Tenloai) VALUES
('Cơm'), ('Phở'), ('Đồ uống'), ('Bánh ngọt'), ('Mì'), ('Gà rán');



/* --------------------------------------------- */
/*   DỮ LIỆU MẪU MÓN ĂN (ĐÚNG NHƯ BẠN YÊU CẦU)  */
/* --------------------------------------------- */
INSERT INTO `monan` (`Mamon`, `Tenmon`, `Giaban`, `Giagoc`, `Noidung`, `Anh`, `Maloai`) VALUES
(3, 'Trà sữa trân châu', 30000.00, 30000.00, 'Trà sữa vị truyền thống, topping trân châu đen', 'assets/img/trasua.jpg', 3),
(4, 'Bánh flan caramel', 20000.00, 20000.00, 'Bánh flan mềm mịn, sốt caramel thơm ngon', 'assets/img/banhflan.jpg', 4),
(5, 'Mì xào hải sản', 55000.00, 55000.00, 'Mì xào với tôm, mực, rau củ tươi ngon', 'assets/img/mixaohaisan.jpg', 5),
(6, 'Gà rán giòn', 35000.00, 35000.00, 'Miếng gà chiên giòn rụm đậm vị', 'assets/img/garan.jpg', 6),
(10, 'Cơm chiên dương châu', 50000.00, 50000.00, 'Cơm chiên với xúc xích, trứng và rau củ', 'assets/img/comchien.jpg', 1),
(13, 'Matcha đá xay', 35000.00, 40000.00, 'Thơm, béo', 'assets/img/1764645153_1764642838_matchadaxay.jpg', 3),
(14, 'Phở gà', 45000.00, 50000.00, 'Gà ta, nước ngọt thanh, có thể bạn sẽ thích', 'assets/img/1764645891_phoga.jpg', 2),
(16, 'Nuôi xào ', 45000.00, 50000.00, 'Ngon', 'assets/img/1764646387_nuoixao.jpg', 1),
(17, 'Mì tương đen', 55000.00, 65000.00, 'Mlem', 'assets/img/1764646432_mituongden.jpg', 5),
(18, 'Spagetti', 60000.00, 65000.00, 'Món tui thích nên tui bán ', 'assets/img/1764646497_spagetti.jpg', 5),
(19, 'Gà sốt béo', 75000.00, 85000.00, 'Ngon lắm ăn đi ', 'assets/img/1764646827_gasotbeo.jpg', 6),
(20, 'Chanh dây', 20000.00, 25000.00, 'Chua uống đau bụng ráng chịu ', 'assets/img/1764646882_chanhday.jpg', 3),
(21, 'Gà sốt kem', 85000.00, 95000.00, 'Béo ăn mập ', 'assets/img/1764647062_gasotkem.jpg', 6),
(22, 'Nước ép xoài chanh leo', 25000.00, 30000.00, 'Trending ', 'assets/img/1764647327_xoaichanhleo.jpg', 3),
(23, 'Pizza hải sản', 120000.00, 130000.00, 'No bể bụng', 'assets/img/1764647370_pizza.jpg', 6),
(24, 'Nước chanh', 20000.00, 25000.00, 'Vitamin C', 'assets/img/1764647397_nuocchanh.jpg', 3),
(25, 'Cơm gà ta đùi góc 4', 65000.00, 75000.00, 'Ăn không dai trả tiền lại ', 'assets/img/1764647634_comgata.jpg', 1),
(26, 'Cơm tấm sà bì chưởng', 45000.00, 55000.00, 'Ẩm thực anh em ', 'assets/img/1764647675_comtam.jpg', 1),
(27, 'Sinh tố xoài', 30000.00, 35000.00, 'Healthy', 'assets/img/1764647709_sinhtoxoai.jpg', 3),
(28, 'Mỳ quảng', 35000.00, 45000.00, 'Đặc sản Đà Nẵng', 'assets/img/1764648227_myquang.jpg', 5),
(29, 'Bánh ướt nóng', 25000.00, 30000.00, 'Ngon', 'assets/img/1764648767_banhuotnong.jpg', 2),
(30, 'Bánh xèo', 30000.00, 35000.00, '6 lá', 'assets/img/1764648824_banhxeo.jpg', 1),
(31, 'Bún bò Huế', 40000.00, 45000.00, 'Ngon', 'assets/img/1764648852_bunbohue.webp', 2),
(32, 'Tiramisu', 45000.00, 50000.00, 'Su béoooo', 'assets/img/1764648931_tiramisu.jpg', 4);



/* --------------------------------------------- */
/*   USERS MẪU                                   */
/* --------------------------------------------- */
INSERT INTO Users (Hoten, Taikhoan, Matkhau, Email, DienthoaiKH, DiachiKH, Ngaysinh, Role) VALUES
('Nguyễn Văn A', 'nguyenvana', '123456', 'vana@gmail.com', '0901234567', '123 Lê Lợi, Q1, TP.HCM', '1999-05-15', 'khach'),
('Trần Thị B', 'tranthib', 'abcdef', 'thib@gmail.com', '0912345678', '45 Nguyễn Huệ, TP.HCM', '2000-07-20', 'khach'),
('Lê Văn C', 'levanc', '654321', 'levanc@gmail.com', '0934567890', '22 Hai Bà Trưng, Hà Nội', '1998-12-10', 'khach'),
('Phạm Thị D', 'phamthid', 'dpassword', 'thid@gmail.com', '0923456789', '78 Phan Chu Trinh, Đà Nẵng', '2002-03-05', 'khach'),
('Nguyễn Quản Lý', 'admin1', 'admin123', NULL, NULL, NULL, NULL, 'admin'),
('Lê Nhân Viên', 'admin2', 'nhanvien01', NULL, NULL, NULL, NULL, 'nhanvien');



/* --------------------------------------------- */
/*   ĐƠN HÀNG MẪU                                */
/* --------------------------------------------- */
INSERT INTO Donhang (TinhtrangDH, Ngaydat, Ngaygiao, UID) VALUES
('Đang xử lý', '2025-11-01 10:30:00', NULL, 1),
('Đã giao', '2025-11-02 12:45:00', '2025-11-03 14:00:00', 2),
('Đang giao', '2025-11-05 09:15:00', NULL, 3),
('Đã hủy', '2025-11-06 18:00:00', NULL, 4);



/* --------------------------------------------- */
/*   CHI TIẾT ĐƠN HÀNG MẪU                       */
/* --------------------------------------------- */
INSERT INTO Chitietdonhang (MaDH, Mamon, Soluong, Dongia) VALUES
(1, 3, 1, 30000),
(1, 5, 1, 55000),
(2, 10, 1, 50000),
(3, 6, 3, 35000),
(4, 14, 1, 45000);



/* --------------------------------------------- */
/*   GIỎ HÀNG MẪU                                */
/* --------------------------------------------- */
INSERT INTO Giohang (UID, Mamon, Soluong) VALUES
(1, 3, 1),
(1, 5, 1),
(2, 10, 1),
(3, 6, 3),
(4, 14, 1);



/* --------------------------------------------- */
/*   BÌNH LUẬN MẪU                               */
/* --------------------------------------------- */
INSERT INTO Binhluan (UID, Mamon, Noidung, Ngaytao) VALUES
(1, 3, 'Ngon, giá hợp lý.', '2025-11-02 14:20:00'),
(2, 5, 'Mì nhiều hải sản.', '2025-11-03 16:00:00'),
(3, 10, 'Cơm chiên ngon.', '2025-11-05 09:30:00'),
(4, 6, 'Gà rán giòn.', '2025-11-06 19:00:00');

