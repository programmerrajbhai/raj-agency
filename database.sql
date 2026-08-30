SET NAMES utf8mb4;
SET time_zone = '+06:00';

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,

    created_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_admins_username (
        username
    )
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS services (
    id INT UNSIGNED
        AUTO_INCREMENT
        PRIMARY KEY,

    title VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL,

    price_basic DECIMAL(10,2)
        UNSIGNED
        NOT NULL,

    file_type ENUM(
        'web',
        'app',
        'ui'
    ) NOT NULL DEFAULT 'web',

    demo_url VARCHAR(2048) NULL,
    thumbnail VARCHAR(2048) NULL,
    short_desc VARCHAR(1000) NULL,
    features JSON NULL,

    is_active TINYINT(1)
        NOT NULL
        DEFAULT 1,

    created_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_services_slug (
        slug
    ),

    KEY idx_services_active_created (
        is_active,
        created_at
    )
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT UNSIGNED
        AUTO_INCREMENT
        PRIMARY KEY,

    order_number VARCHAR(32)
        NOT NULL,

    full_name VARCHAR(100)
        NOT NULL,

    email VARCHAR(190)
        NOT NULL,

    phone VARCHAR(30)
        NOT NULL,

    country VARCHAR(80)
        NOT NULL,

    notes VARCHAR(1000)
        NULL,

    total_amount DECIMAL(10,2)
        UNSIGNED
        NOT NULL,

    status ENUM(
        'pending',
        'confirmed',
        'processing',
        'completed',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',

    created_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uq_orders_order_number (
        order_number
    ),

    KEY idx_orders_email (
        email
    ),

    KEY idx_orders_status_created (
        status,
        created_at
    )
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
    id BIGINT UNSIGNED
        AUTO_INCREMENT
        PRIMARY KEY,

    order_id BIGINT UNSIGNED
        NOT NULL,

    service_id INT UNSIGNED
        NULL,

    service_title VARCHAR(150)
        NOT NULL,

    unit_price DECIMAL(10,2)
        UNSIGNED
        NOT NULL,

    quantity SMALLINT UNSIGNED
        NOT NULL
        DEFAULT 1,

    line_total DECIMAL(10,2)
        UNSIGNED
        NOT NULL,

    created_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_order_items_service
        FOREIGN KEY (service_id)
        REFERENCES services(id)
        ON DELETE SET NULL,

    KEY idx_order_items_order (
        order_id
    ),

    KEY idx_order_items_service (
        service_id
    )
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED
        AUTO_INCREMENT
        PRIMARY KEY,

    name VARCHAR(100)
        NOT NULL,

    email VARCHAR(190)
        NOT NULL,

    service VARCHAR(150)
        NULL,

    message TEXT
        NOT NULL,

    status ENUM(
        'new',
        'read',
        'replied',
        'archived'
    ) NOT NULL DEFAULT 'new',

    created_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    KEY idx_messages_status_created (
        status,
        created_at
    ),

    KEY idx_messages_email (
        email
    )
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;