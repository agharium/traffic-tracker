#!/bin/bash

# Replace PORT placeholder in Apache config
sed -i "s/\${PORT}/${PORT}/" /etc/apache2/sites-available/000-default.conf
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf

# Start Apache
exec apache2-foreground
