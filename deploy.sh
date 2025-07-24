#!/bin/bash

echo "⏳ Instalando dependências do Composer..."
composer2 update --no-dev --optimize-autoloader

echo "🔄 Executando migrations..."
php artisan migrate --force

echo "🔗 Criando link simbólico de storage..."
php artisan storage:link
npm run build
echo "✅ Deploy finalizado com sucesso!"
