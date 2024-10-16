DB_USER="root"
DB_PASS=""
DB_NAME="hexagon"
SQL_FILE="data_sistem_keuangan.sql"

echo "Creating database $DB_NAME..."
mysql -u$DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;"

if [ $? -ne 0 ]; then
    echo "Failed to create database $DB_NAME!"
    exit 1
fi

echo "Importing data from $SQL_FILE into $DB_NAME..."
mysql -u$DB_USER -p$DB_PASS $DB_NAME < $SQL_FILE

if [ $? -ne 0 ]; then
    echo "Failed to import data from $SQL_FILE into $DB_NAME!"
    exit 1
fi

echo "Database setup completed."

php artisan migrate