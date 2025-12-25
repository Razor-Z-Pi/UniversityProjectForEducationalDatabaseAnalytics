-- Обощённая_группа_товаров (родительская категория товаров)
CREATE TABLE IF NOT EXISTS Обощённая_группа_товаров (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Наименование VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_наименование (Наименование)
);

-- Группа (подкатегория товаров)
CREATE TABLE IF NOT EXISTS Группа (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Наименование VARCHAR(100) NOT NULL,
    id_обобщ_группа INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_обобщ_группа) 
        REFERENCES Обощённая_группа_товаров(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    UNIQUE KEY uq_группа_в_обобщённой (Наименование, id_обобщ_группа)
);

-- Артикулы (товары)
CREATE TABLE IF NOT EXISTS Артикулы (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Артикул VARCHAR(50) NOT NULL,
    Наименование VARCHAR(200) NOT NULL,
    id_группы INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_группы) 
        REFERENCES Группа(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    UNIQUE KEY uq_артикул (Артикул),
    UNIQUE KEY uq_наименование_в_группе (Наименование, id_группы)
);

-- ед_измерения (единицы измерения)
CREATE TABLE IF NOT EXISTS ед_измерения (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Наименование VARCHAR(50) NOT NULL,
    сокращение VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_наименование (Наименование)
);

-- Города (географический справочник)
CREATE TABLE IF NOT EXISTS Города (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Город VARCHAR(100) NOT NULL,
    Экономический_район VARCHAR(100) NOT NULL,
    Федеральный_округ VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_город_регион (Город, Экономический_район)
);

-- типы_клиентов (классификатор клиентов)
CREATE TABLE IF NOT EXISTS типы_клиентов (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Наименование VARCHAR(100) NOT NULL,
    приоритет TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_наименование (Наименование)
);

-- Скидки (купоны/акции/скидки)
CREATE TABLE IF NOT EXISTS Скидки (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Дата_продажи DATE NOT NULL,
    id_город INT NOT NULL,
    id_типы_клиентов INT NOT NULL,
    скидки_по_группе_клиентов DECIMAL(5,2) DEFAULT 0.00,
    скидки_по_группе_сумме_покупки DECIMAL(5,2) DEFAULT 0.00,
    общая_скидка DECIMAL(5,2) AS (скидки_по_группе_клиентов + скидки_по_группе_сумме_покупки) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_город) 
        REFERENCES Города(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (id_типы_клиентов) 
        REFERENCES типы_клиентов(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Клиенты (транзакции/продажи)
CREATE TABLE IF NOT EXISTS Клиенты (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Дата_продажи DATE NOT NULL,
    Номер_клиента VARCHAR(50) NOT NULL,
    id_группа INT NOT NULL,
    id_ед_измерения INT NOT NULL,
    id_артикулы INT NOT NULL,
    id_скидки INT NOT NULL,
    цена_за_ед DECIMAL(12,2) NOT NULL,
    количество DECIMAL(10,3) NOT NULL,
    сумма_без_скидки DECIMAL(15,2) AS (цена_за_ед * количество) STORED,
    -- Временное поле, которое будет заполняться триггером
    общая_скидка_процент DECIMAL(5,2),
    сумма_со_скидкой DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_группа) 
        REFERENCES Группа(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (id_ед_измерения) 
        REFERENCES ед_измерения(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (id_артикулы) 
        REFERENCES Артикулы(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (id_скидки) 
        REFERENCES Скидки(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- Создание триггеров для автоматического расчета скидок
DELIMITER //

-- Триггер для заполнения общей скидки и суммы со скидкой при вставке
CREATE TRIGGER before_clients_insert_calculate_discount
BEFORE INSERT ON Клиенты
FOR EACH ROW
BEGIN
    DECLARE discount_percent DECIMAL(5,2);
    
    -- Получаем процент скидки
    SELECT общая_скидка INTO discount_percent 
    FROM Скидки 
    WHERE id = NEW.id_скидки;
    
    -- Сохраняем процент скидки
    SET NEW.общая_скидка_процент = discount_percent;
    
    -- Рассчитываем сумму со скидкой
    SET NEW.сумма_со_скидкой = (NEW.цена_за_ед * NEW.количество) * (1 - discount_percent / 100);
END//

-- Триггер для обновления скидки при изменении записи
CREATE TRIGGER before_clients_update_calculate_discount
BEFORE UPDATE ON Клиенты
FOR EACH ROW
BEGIN
    DECLARE discount_percent DECIMAL(5,2);
    
    -- Если изменилась скидка, пересчитываем
    IF NEW.id_скидки != OLD.id_скидки OR NEW.цена_за_ед != OLD.цена_за_ед OR NEW.количество != OLD.количество THEN
        -- Получаем процент скидки
        SELECT общая_скидка INTO discount_percent 
        FROM Скидки 
        WHERE id = NEW.id_скидки;
        
        -- Сохраняем процент скидки
        SET NEW.общая_скидка_процент = discount_percent;
        
        -- Рассчитываем сумму со скидкой
        SET NEW.сумма_со_скидкой = (NEW.цена_за_ед * NEW.количество) * (1 - discount_percent / 100);
    END IF;
END//

-- Триггер для проверки цены
CREATE TRIGGER before_clients_insert_check_price
BEFORE INSERT ON Клиенты
FOR EACH ROW
BEGIN
    IF NEW.цена_за_ед < 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Цена за единицу не может быть отрицательной';
    END IF;
    
    IF NEW.количество <= 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Количество должно быть больше 0';
    END IF;
END//

CREATE TRIGGER before_clients_update_check_price
BEFORE UPDATE ON Клиенты
FOR EACH ROW
BEGIN
    IF NEW.цена_за_ед < 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Цена за единицу не может быть отрицательной';
    END IF;
    
    IF NEW.количество <= 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Количество должно быть больше 0';
    END IF;
END//

-- Триггеры для проверки скидок
CREATE TRIGGER before_скидки_insert_check_discount
BEFORE INSERT ON Скидки
FOR EACH ROW
BEGIN
    IF NEW.скидки_по_группе_клиентов < 0 OR NEW.скидки_по_группе_клиентов > 100 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Скидка по группе клиентов должна быть от 0 до 100%';
    END IF;
    
    IF NEW.скидки_по_группе_сумме_покупки < 0 OR NEW.скидки_по_группе_сумме_покупки > 100 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Скидка по сумме покупки должна быть от 0 до 100%';
    END IF;
END//

CREATE TRIGGER before_скидки_update_check_discount
BEFORE UPDATE ON Скидки
FOR EACH ROW
BEGIN
    IF NEW.скидки_по_группе_клиентов < 0 OR NEW.скидки_по_группе_клиентов > 100 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Скидка по группе клиентов должна быть от 0 до 100%';
    END IF;
    
    IF NEW.скидки_по_группе_сумме_покупки < 0 OR NEW.скидки_по_группе_сумме_покупки > 100 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Скидка по сумме покупки должна быть от 0 до 100%';
    END IF;
END//

DELIMITER ;

-- Создание индексов
CREATE INDEX idx_общ_группа_наим ON Обощённая_группа_товаров(Наименование);
CREATE INDEX idx_группа_наим ON Группа(Наименование);
CREATE INDEX idx_группа_обобщ ON Группа(id_обобщ_группа);
CREATE INDEX idx_артикул_код ON Артикулы(Артикул);
CREATE INDEX idx_артикул_наим ON Артикулы(Наименование);
CREATE INDEX idx_артикул_группа ON Артикулы(id_группы);
CREATE INDEX idx_ед_изм_наим ON ед_измерения(Наименование);
CREATE INDEX idx_город_название ON Города(Город);
CREATE INDEX idx_город_район ON Города(Экономический_район);
CREATE INDEX idx_город_округ ON Города(Федеральный_округ);
CREATE INDEX idx_тип_клиента_наим ON типы_клиентов(Наименование);
CREATE INDEX idx_тип_клиента_приоритет ON типы_клиентов(приоритет);
CREATE INDEX idx_скидка_дата ON Скидки(Дата_продажи);
CREATE INDEX idx_скидка_город ON Скидки(id_город);
CREATE INDEX idx_скидка_тип_клиента ON Скидки(id_типы_клиентов);
CREATE INDEX idx_скидка_общая ON Скидки(общая_скидка);
CREATE INDEX idx_клиенты_дата ON Клиенты(Дата_продажи);
CREATE INDEX idx_клиенты_номер ON Клиенты(Номер_клиента);
CREATE INDEX idx_клиенты_артикул ON Клиенты(id_артикулы);
CREATE INDEX idx_клиенты_скидка ON Клиенты(id_скидки);
CREATE INDEX idx_клиенты_группа ON Клиенты(id_группа);
CREATE INDEX idx_клиенты_сумма_без_скидки ON Клиенты(сумма_без_скидки);
CREATE INDEX idx_клиенты_сумма_со_скидкой ON Клиенты(сумма_со_скидкой);
CREATE INDEX idx_клиенты_скидка_процент ON Клиенты(общая_скидка_процент);

-- Создание представлений

CREATE OR REPLACE VIEW vw_детализация_продаж AS
SELECT 
    к.id,
    к.Дата_продажи,
    к.Номер_клиента,
    к.цена_за_ед,
    к.количество,
    к.сумма_без_скидки,
    к.сумма_со_скидкой,
    к.общая_скидка_процент AS Скидка_процент,
    (к.сумма_без_скидки - к.сумма_со_скидкой) AS Сумма_скидки,
    арт.Артикул,
    арт.Наименование AS Наименование_товара,
    гр.Наименование AS Группа_товара,
    огт.Наименование AS Общая_группа_товаров,
    ед.Наименование AS Единица_измерения,
    гор.Город,
    гор.Экономический_район,
    гор.Федеральный_округ,
    тк.Наименование AS Тип_клиента
FROM Клиенты к
JOIN Артикулы арт ON к.id_артикулы = арт.id
JOIN Группа гр ON к.id_группа = гр.id
JOIN Обощённая_группа_товаров огт ON гр.id_обобщ_группа = огт.id
JOIN ед_измерения ед ON к.id_ед_измерения = ед.id
JOIN Скидки ск ON к.id_скидки = ск.id
JOIN Города гор ON ск.id_город = гор.id
JOIN типы_клиентов тк ON ск.id_типы_клиентов = тк.id;

-- Вставка тестовых данных
INSERT INTO Обощённая_группа_товаров (Наименование) VALUES
('Электроника'),
('Бытовая техника'),
('Мебель'),
('Одежда');

INSERT INTO Группа (Наименование, id_обобщ_группа) VALUES
('Смартфоны', 1),
('Ноутбуки', 1),
('Телевизоры', 1),
('Холодильники', 2),
('Стиральные машины', 2),
('Диваны', 3),
('Кресла', 3),
('Куртки', 4),
('Брюки', 4);

INSERT INTO ед_измерения (Наименование, сокращение) VALUES
('Штука', 'шт'),
('Килограмм', 'кг'),
('Литр', 'л'),
('Метр', 'м');

INSERT INTO Города (Город, Экономический_район, Федеральный_округ) VALUES
('Москва', 'Центральный', 'Центральный'),
('Санкт-Петербург', 'Северо-Западный', 'Северо-Западный'),
('Новосибирск', 'Сибирский', 'Сибирский'),
('Екатеринбург', 'Уральский', 'Уральский');

INSERT INTO типы_клиентов (Наименование, приоритет) VALUES
('Физическое лицо', 1),
('Малый бизнес', 2),
('Корпоративный клиент', 3),
('VIP клиент', 4);

INSERT INTO Артикулы (Артикул, Наименование, id_группы) VALUES
('SM-001', 'iPhone 15 Pro', 1),
('SM-002', 'Samsung Galaxy S24', 1),
('NB-001', 'MacBook Pro 16"', 2),
('TV-001', 'LG OLED 65"', 3),
('FR-001', 'Холодильник Bosch', 4),
('WM-001', 'Стиральная машина Samsung', 5);

INSERT INTO Скидки (Дата_продажи, id_город, id_типы_клиентов, скидки_по_группе_клиентов, скидки_по_группе_сумме_покупки) VALUES
('2024-01-15', 1, 1, 5.00, 2.00),
('2024-01-15', 1, 2, 7.00, 3.00),
('2024-01-16', 2, 1, 3.00, 1.00),
('2024-01-16', 2, 3, 10.00, 5.00),
('2024-01-17', 3, 4, 15.00, 7.00);

INSERT INTO Клиенты (Дата_продажи, Номер_клиента, id_группа, id_ед_измерения, id_артикулы, id_скидки, цена_за_ед, количество) VALUES
('2024-01-15', 'CL001', 1, 1, 1, 1, 89999.00, 1.000),
('2024-01-15', 'CL002', 1, 1, 2, 2, 74999.00, 2.000),
('2024-01-16', 'CL003', 2, 1, 3, 3, 159999.00, 1.000),
('2024-01-16', 'CL004', 3, 1, 4, 4, 129999.00, 1.000),
('2024-01-17', 'CL005', 4, 1, 5, 5, 69999.00, 3.000);

SELECT * FROM vw_детализация_продаж;

SELECT 
    Дата_продажи,
    COUNT(*) AS Количество_продаж,
    SUM(количество) AS Общее_количество,
    SUM(сумма_без_скидки) AS Сумма_без_скидки,
    SUM(сумма_со_скидкой) AS Сумма_со_скидкой,
    SUM(сумма_без_скидки - сумма_со_скидкой) AS Общая_скидка
FROM Клиенты
GROUP BY Дата_продажи
ORDER BY Дата_продажи;