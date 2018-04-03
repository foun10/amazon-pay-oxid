#!/usr/bin/env bash
set -euo pipefail

service mysql start
echo -ne "Waiting for mysql service"
while ! mysqladmin ping -h"127.0.0.1" -P 3306 --silent; do
    echo -ne "."
    sleep 1
done
echo ""
echo "=== Set mysql root password ==="
mysqladmin -u root password dbpass
echo "=== Generate default database ==="
mysql -h127.0.0.1 -uroot -pdbpass -e "CREATE DATABASE oxidehop_ce"
mysql -h127.0.0.1 -uroot -pdbpass -Doxidehop_ce << QUERY_INPUT
SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION';
SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';
QUERY_INPUT

echo "=== Start build ==="

CURRENT_DIR=${PWD}
TEMP_DIR='/tmp/build'
SHOP_DIR="${TEMP_DIR}/oxideshop_ce/source"
VENDOR_DIR="${SHOP_DIR}/modules/bestit"
MODULE_BASE_DIR="${VENDOR_DIR}/amazonpay4oxid"

# Create tmp dir
if [[ -d ${TEMP_DIR} ]]; then
    rm -R -f ${TEMP_DIR}
fi

mkdir -p ${TEMP_DIR}
cd ${TEMP_DIR}

# Setup shop
echo "=== Setup shop ==="
git clone https://github.com/OXID-eSales/oxideshop_ce.git --branch b-5.3-ce
composer install -d ${SHOP_DIR} --ignore-platform-reqs
sed -i 's|<dbHost_ce>|127.0.0.1|; s|<dbName_ce>|oxidehop_ce|; s|<dbUser_ce>|root|; s|<dbPwd_ce>|dbpass|; s|<sShopURL_ce>|http://127.0.0.1|; s|<sShopDir_ce>|'${SHOP_DIR}/'|; s|<sCompileDir_ce>|'${SHOP_DIR}'/tmp|; s|<iUtfMode>|0|; s|$this->iDebug = 0|$this->iDebug = 1|; s|mysql|mysqli|' ${SHOP_DIR}/config.inc.php
wget "https://raw.githubusercontent.com/OXID-eSales/oxideshop_demodata_ce/b-5.3/src/demodata.sql" -P oxideshop_ce/source/setup/sql/
cp ${CURRENT_DIR}/test_config.yml ${SHOP_DIR}

# Setup flow theme
echo "=== Setup flow theme ==="
cd oxideshop_ce/source/application/views
git clone https://github.com/OXID-eSales/flow_theme.git flow --branch b-1.0
cp -R flow/out/flow ../../out/

# Copy amazon module
echo "=== Copy amazon module ==="
mkdir -p ${MODULE_BASE_DIR}
cp -R ${CURRENT_DIR}/* ${MODULE_BASE_DIR}
cat <<EOF > ${VENDOR_DIR}/vendormetadata.php
<?php
\$sVendorMetadataVersion = '1.0';
EOF
composer install -d ${MODULE_BASE_DIR} --ignore-platform-reqs

# Setup and run unit tests
echo "=== Setup unit tests and run them ==="
TEST_SUITE="${MODULE_BASE_DIR}/tests/"
cd ${MODULE_BASE_DIR}/tests/
${SHOP_DIR}/vendor/oxid-esales/testing-library/bin/runtests
