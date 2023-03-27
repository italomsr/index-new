<?php
require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

function index_create_menu() {
    add_options_page('Indexação de URLs', 'Indexação de URLs', 'manage_options', 'index-urls', 'index_admin_page');
}
add_action('admin_menu', 'index_create_menu');

function index_admin_page() {

    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    if (!current_user_can('manage_options')) {
        wp_die(__('Você não tem permissão para acessar esta página.'));
    }

    $api_key_updated = false;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['index_google_json_key'])) {
            update_option('index_google_json_key', trim(stripslashes($_POST['index_google_json_key'])));
            $api_key_updated = true;
        }
        if (isset($_POST['urls']) && isset($_POST['action'])) {
            $urls = array_filter(array_map('trim', explode("\n", $_POST['urls'])));
            indexacao_submit_urls($urls, $_POST['action']);
        }
    }

    $index_google_json_key = get_option('index_google_json_key');

    if ($api_key_updated) {
        echo '<div class="notice notice-success is-dismissible"><p>Chave da API JSON atualizada com sucesso!</p></div>';
    }

    function get_non_indexed_posts() {
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => 20,
            'post_status' => 'publish',
        );
    
        $non_indexed_posts = new WP_Query($args);
    
        return $non_indexed_posts;
    }
    
    ?>
   <div class="wrap">
    <h1>Indexação de URLs</h1>
    
    <div id="indexacao-tabs">
        <ul>
            <li><a href="#indexing-tab">URLs</a></li>
            <li><a href="#api-key-tab">Chave da API JSON</a></li>
            <li><a href="#posts-tab">Posts Recentes</a></li>
            <li><a href="#error-log-tab">Log de Erros</a></li>
        </ul>
        <div id="indexing-tab">
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="urls">URLs para indexar</label></th>
                        <td>
                            <textarea name="urls" rows="10" cols="50" id="urls" class="large-text code" required></textarea>
                            <p class="description">Insira uma URL por linha, as URLs devem começar com "http://" ou "https://".<br> API possui um limite padrão de 200 URLs por dia.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Ação</th>
                        <td>
                            <select name="action">
                                <option value="URL_SUBMITTED">Enviar URL</option>
                                <option value="URL_UPDATED">Atualizar URL</option>
                                <option value="URL_STATUS">Verificar status da URL</option>
                                <option value="URL_DELETED">Remover URL</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Enviar">
                </p>
            </form>
        </div>
        <div id="api-key-tab">
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="index_google_json_key">Chave da API JSON</label></th>
                        <td>
                            <textarea name="index_google_json_key" rows="10" cols="50" id="index_google_json_key" class="large-text code" required><?php echo esc_textarea($index_google_json_key); ?></textarea>
                            <p class="description">Cole aqui a chave da API JSON obtida no Google Cloud Console, siga <a href="https://developers.google.com/search/apis/indexing-api/v3/quickstart?hl=pt-br#get-started" target="_blank">os passos</a> para gerar a chave.
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="Salvar API">
                </p>
            </form>
        </div>
        <div id="posts-tab">
            <h2>Posts Recentes (20 posts)</h2>
            <?php
            $non_indexed_posts = get_non_indexed_posts();
            if ($non_indexed_posts->have_posts()) {
                ?>
                <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-title">Título</th>
                            <th scope="col" class="manage-column column-url">URL</th>
                            <th scope="col" class="manage-column column-link">Link</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($non_indexed_posts->have_posts()) {
                        $non_indexed_posts->the_post();
                        $post_permalink = get_permalink();
                        ?>
                        <tr data-url="<?php echo esc_url($post_permalink); ?>">
                            <td class="column-title"><?php echo get_the_title(); ?></td>
                            <td class="column-url"><?php echo esc_url($post_permalink); ?></td>
                            <td class="column-link"><a href="<?php echo esc_url($post_permalink); ?>" target="_blank">Visualizar post</a></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <button id="copy-urls-btn" class="button" style="margin-top: 12px;">Copiar URLs</button>
                <input type="hidden" id="urls-to-copy" value="">

                <?php
                wp_reset_postdata();
            } else {
                echo '<p>Nenhum post não indexado encontrado.</p>';
            }
            ?>
        </div>
        <div id="error-log-tab">
            <?php
            function read_log_file($file_path) {
                $lines = [];
                if (file_exists($file_path)) {
                    $file_content = file_get_contents($file_path);
                    $lines = explode(PHP_EOL, trim($file_content));
                    usort($lines, function($a, $b) {
                        $date_a = substr($a, 1, 19);
                        $date_b = substr($b, 1, 19);
                        return strcmp($date_b, $date_a);
                    });
                }
                return $lines;
            }

            $log_file = plugin_dir_path(__FILE__) . 'error_log.txt';
            $log_lines = read_log_file($log_file);
            $log_content = implode(PHP_EOL, $log_lines);

            if (!empty($log_lines)) {
                ?>
                <h2>Log de Erros</h2>
                <textarea readonly rows="10" cols="50" class="large-text code"><?php echo esc_textarea($log_content); ?></textarea>
                <a href="<?php echo plugin_dir_url(__FILE__) . 'error_log.txt'; ?>" download class="button" style="margin-top: 12px;">Baixar Log</a>
                <?php
            } else {
                echo '<p>Nenhum log de erros encontrado.</p>';
            } ?>
        </div>
    </div>    
    <script>
        jQuery(document).ready(function($) {
            $("#indexacao-tabs").tabs();
             function copyToClipboard(text) {
                var $temp = $("<textarea>");
                $("body").append($temp);
                $temp.val(text).select();
                document.execCommand("copy");
                $temp.remove();
            }
            function showSuccessMessage(message) {
                var $successNotice = $('<div class="notice notice-success is-dismissible"><p></p></div>');
                $successNotice.find("p").text(message);
                $(".wrap h1").after($successNotice);

                setTimeout(function() {
                    $successNotice.fadeOut("slow", function() {
                        $(this).remove();
                    });
                }, 3000);
            }
            $("#copy-urls-btn").on("click", function() {
                var urls = [];
                $("table tbody tr").each(function() {
                    var url = $(this).data("url");
                    if (url) {
                        urls.push(url);
                    }
                });

                var lineBreak = navigator.platform.indexOf("Win") !== -1 ? "\r\n" : "\n";
                var urlsText = urls.join(lineBreak);
                $("#urls-to-copy").val(urlsText);
                copyToClipboard(urlsText);

                showSuccessMessage("URLs copiadas para a área de transferência!");

            });
        });
    </script>
</div>

<?php
}
    function indexacao_submit_urls($urls, $action) {
        $index_google_json_key = get_option('index_google_json_key');

        if (empty($index_google_json_key)) {
            echo '<div class="notice notice-error is-dismissible"><p>Erro: Chave da API JSON não configurada.</p></div>';
            return;
        }
        
        $client = new Google_Client();
        $client->setAuthConfig(json_decode($index_google_json_key, true));
        $client->addScope(Google_Service_Indexing::INDEXING);
        $indexingService = new Google_Service_Indexing($client);

        $success = 0;
        $failure = 0;

        $log_file = plugin_dir_path(__FILE__) . 'error_log.txt'; 
        if (file_exists($log_file)) {
        
        foreach ($urls as $url) {
            try {
                if ($action === 'URL_STATUS') {
                    $optParams = array('url' => $url);
                    $urlStatus = $indexingService->urlNotifications->getMetadata($optParams);
                    $publishState = $urlStatus->getLatestRemove() ? "Removido" : "Indexado";
                    echo '<div class="notice notice-info"><p>Status da URL <strong>' . esc_html($url) . '</strong>: ' . esc_html($publishState) . '</p></div>';
                    continue;
                }

                $urlNotification = new Google_Service_Indexing_UrlNotification();
                $urlNotification->setUrl($url);
                $urlNotification->setType($action === 'URL_SUBMITTED' ? 'URL_UPDATED' : $action);
                $indexingService->urlNotifications->publish($urlNotification);
                $success++;
            
            } catch (Exception $e) {
                $error_message = '[' . date('Y-m-d H:i:s') . '] Erro ao processar a URL ' . $url . ': ' . $e->getMessage() . PHP_EOL;
                file_put_contents($log_file, $error_message, FILE_APPEND);
                $failure++;
            }
        }
    }
    if ($action !== 'URL_STATUS') {
        echo '<div class="notice notice-success is-dismissible"><p>' . $success . ' URLs processadas com sucesso.</p></div>';

        if ($failure > 0) {
            echo '<div class="notice notice-error is-dismissible"><p>Erro ao processar ' . $failure . ' URLs.</p></div>';
        }
    }
}