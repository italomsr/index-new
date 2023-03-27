# Plugin WP Index

WP Index é um plugin WordPress para indexação de URLs do Google. 

Ele possui as seguintes funções:

1. `index_create_menu()`: Adiciona uma página de opções no menu administrativo do WordPress.
2. `index_admin_page()`: Exibe a página de opções do plugin, permitindo que o usuário insira a chave da API JSON e URLs para indexação.
3. `get_non_indexed_posts()`: Retorna os 20 posts mais recentes que ainda não foram indexados.
4. `read_log_file($file_path)`: Lê o arquivo de log de erros e retorna seu conteúdo como um array de linhas.
5. `indexacao_submit_urls($urls, $action)`: Submete as URLs fornecidas para a API de indexação do Google, executando a ação especificada (enviar, atualizar, verificar status ou remover).

O código também possui lógica para lidar com requisições POST, atualizando a chave da API JSON e processando as URLs fornecidas pelo usuário. Ele também inclui JavaScript para exibir guias na página de opções e copiar URLs de posts não indexados para a área de transferência.
