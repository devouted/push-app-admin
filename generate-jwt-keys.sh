#!/bin/bash

set -e

JWT_DIR="app/config/jwt"
PASSPHRASE=${JWT_PASSPHRASE:-changeme}

echo "🔐 Generating JWT keys..."

mkdir -p $JWT_DIR

openssl genpkey -algorithm RSA -out $JWT_DIR/private.pem -pkeyopt rsa_keygen_bits:4096 -aes256 -pass pass:$PASSPHRASE

openssl rsa -pubout -in $JWT_DIR/private.pem -out $JWT_DIR/public.pem -passin pass:$PASSPHRASE

chmod 600 $JWT_DIR/private.pem
chmod 644 $JWT_DIR/public.pem

echo "✅ JWT keys generated successfully!"
