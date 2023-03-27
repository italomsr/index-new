# Plugin WP Index

**WP Index** é um plugin WordPress para indexação de URLs do Google. 

Ele possui as seguintes funções:

1. `index_create_menu()`: Adiciona uma página de opções no menu administrativo do WordPress.
2. `index_admin_page()`: Exibe a página de opções do plugin, permitindo que o usuário insira a chave da API JSON e URLs para indexação.
3. `get_non_indexed_posts()`: Retorna os 20 posts mais recentes que ainda não foram indexados.
4. `read_log_file($file_path)`: Lê o arquivo de log de erros e retorna seu conteúdo como um array de linhas.
5. `indexacao_submit_urls($urls, $action)`: Submete as URLs fornecidas para a API de indexação do Google, executando a ação especificada (enviar, atualizar, verificar status ou remover).

O código também possui lógica para lidar com requisições POST, atualizando a chave da API JSON e processando as URLs fornecidas pelo usuário. Ele também inclui JavaScript para exibir guias na página de opções e copiar URLs de posts não indexados para a área de transferência.

# Como usar
1. Esse plugin usa os arquivos da integração do [Google APIs](https://github.com/googleapis/google-api-php-client/releases).
2. Extraia os arquivos `src` e `vendor` arquivos e insira na pasta raiz do plugin, a estrutura das pastas ficará assim: 

```
. wp-index
├── src
├── vendor
├── error_log.txt
├── index-admin-page.php
└── wp-index-url.php
```
3. Faça o upload para dentro da pasta do WordPress `wp-content/plugins`.
4. Crie o JSON da API, siga os [passos aqui](https://developers.google.com/search/apis/indexing-api/v3/quickstart?hl=pt-br#get-started). 
5. Ative o plugin e insira **Chave API JSON**.

# Telas:
![URLs](https://user-images.githubusercontent.com/84940616/228016050-5e8301b7-cc4a-4a05-b02f-ea8c95e27f45.png)
![Chave-API](https://user-images.githubusercontent.com/84940616/228016493-9b235ce0-0383-4d6b-84be-d4f96918bb5c.png)
![posts-recentes](https://user-images.githubusercontent.com/84940616/228016089-21ab335d-6256-4c55-b615-3f8eeaa3103e.png)
![erros](https://user-images.githubusercontent.com/84940616/228016101-205fe302-1b52-410e-bd06-8f2d09859821.png)

