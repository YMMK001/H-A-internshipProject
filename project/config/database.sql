


CREATE TABLE `users`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NULL,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(255) NOT NULL,
    `nrc` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) NOT NULL
);
ALTER TABLE
    `users` ADD UNIQUE `users_email_unique`(`email`);
CREATE TABLE `rental_houses`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `city` VARCHAR(100) NOT NULL,
    `township` VARCHAR(100) NOT NULL,
    `full_address` TEXT NOT NULL,
    `rentable_type` VARCHAR(50) NOT NULL,
    `is_active` BOOLEAN NULL DEFAULT 1,
    `amenities` TEXT NULL
);
CREATE TABLE `apartments`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rental_house_id` INT NOT NULL,
    `max_occupy` INT NOT NULL,
    `floor_level` VARCHAR(50) NULL,
    `apartment_price` DECIMAL(10, 2) NOT NULL,
    `is_available` BOOLEAN NULL DEFAULT 1,
    `deposit_amount`DECIMAL(10, 2) NOT NULL ,
   
);
CREATE TABLE `hostel_rooms`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rental_house_id` INT NOT NULL,
    `room_num` VARCHAR(50) NOT NULL,
    `room_type` VARCHAR(100) NULL,
    `sub_unit` VARCHAR(50) NULL,
    `monthly_price` DECIMAL(10, 2) NOT NULL,
    `is_available` BOOLEAN NULL DEFAULT 1,
     `deposit_amount`DECIMAL(10, 2) NOT NULL,
     
);
CREATE TABLE `contracts`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `apartment_id` INT NULL,
    `hostel_room_id` INT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `total_deposit_amount` DECIMAL(10, 2) NOT NULL
);
CREATE TABLE `installments`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT NOT NULL,
    `installment_period` INT NOT NULL,
    `amount_to_pay` DECIMAL(10, 2) NOT NULL,
    `due_date` DATE NOT NULL,
    `status` ENUM('unpaid', 'partially_paid', 'paid') NULL DEFAULT 'unpaid',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(), `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP());
CREATE TABLE `payment_methods`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `account_name` VARCHAR(255) NOT NULL,
    `account_number` VARCHAR(100) NOT NULL,
    `is_active` BOOLEAN NULL DEFAULT 1
);
CREATE TABLE `rental_house_images` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `rental_house_id` INT NOT NULL,
    `image_url` VARCHAR(255) NOT NULL,
    `is_cover` BOOLEAN NULL DEFAULT 0,
    CONSTRAINT `fk_images_rental_houses` FOREIGN KEY (`rental_house_id`) REFERENCES `rental_houses`(`id`) ON DELETE CASCADE
);
CREATE TABLE `payments`(
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `installment_id` INT NOT NULL,
    `payment_method_id` INT NOT NULL,
    `paid_amount` BIGINT NOT NULL,
    `payment_image` VARCHAR(255) NULL,
    `paid_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP());
    
ALTER TABLE
    `contracts` ADD CONSTRAINT `contracts_hostel_room_id_foreign` FOREIGN KEY(`hostel_room_id`) REFERENCES `hostel_rooms`(`id`);
ALTER TABLE
    `hostel_rooms` ADD CONSTRAINT `hostel_rooms_rental_house_id_foreign` FOREIGN KEY(`rental_house_id`) REFERENCES `rental_houses`(`id`);
ALTER TABLE
    `installments` ADD CONSTRAINT `installments_contract_id_foreign` FOREIGN KEY(`contract_id`) REFERENCES `contracts`(`id`);
ALTER TABLE
    `rental_houses` ADD CONSTRAINT `rental_houses_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);
ALTER TABLE
    `payments` ADD CONSTRAINT `payments_payment_method_id_foreign` FOREIGN KEY(`payment_method_id`) REFERENCES `payment_methods`(`id`);
ALTER TABLE
    `apartments` ADD CONSTRAINT `apartments_rental_house_id_foreign` FOREIGN KEY(`rental_house_id`) REFERENCES `rental_houses`(`id`);
ALTER TABLE
    `contracts` ADD CONSTRAINT `contracts_apartment_id_foreign` FOREIGN KEY(`apartment_id`) REFERENCES `apartments`(`id`);
ALTER TABLE
    `payments` ADD CONSTRAINT `payments_installment_id_foreign` FOREIGN KEY(`installment_id`) REFERENCES `installments`(`id`);
ALTER TABLE
    `contracts` ADD CONSTRAINT `contracts_user_id_foreign` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`);