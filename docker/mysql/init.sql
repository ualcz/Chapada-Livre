-- ============================================
-- Chapada Livre - Inicialização do MySQL
-- ============================================
-- Este script roda automaticamente na primeira
-- vez que o container do MySQL é criado.
-- Concede todas as permissões ao usuário laravel.

GRANT ALL PRIVILEGES ON *.* TO 'laravel'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
