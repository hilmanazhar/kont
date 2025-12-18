FROM php:8.2-cli

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create uploads directories
RUN mkdir -p uploads/info uploads/qris uploads/receipts uploads/settlements
RUN chmod -R 777 uploads

# Expose port
EXPOSE 8080

# Start PHP built-in server
CMD ["php", "-S", "0.0.0.0:8080", "router.php"]
