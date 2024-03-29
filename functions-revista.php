<?php
/*
 *
 * Revista Agriculturas
 *
 * Funções relacionadas com a Revista: criação de post type, registro de taxonomias e cadastro de meta boxes
 *
 */
 
// Cria o post type 'revista'
function revista_create_post_type() {

	$args = array(
		'labels' => array(
			'name' 			=> 'Arquivo da Revista Agriculturas',
			'singular_name' => 'Revista Arquivada',
			'add_new'		=> 'Adicionar nova Revista ou Publicação Arquivada',
			'add_new_item'	=> 'Adicionar nova Revista ou Publicação Arquivada',
			'edit_item'		=> 'Editar Revista ou Publicação Arquivada',
			'view_item'		=> 'Visualizar Revista ou Publicação Arquivada'
		),

		//'menu_position'		=> 5,
		'public' 			=> true,
		'has_archive'		=> true,
		'supports'			=> array( 'title', 'author', 'editor', 'excerpt', 'comments','page-attributes' ),
		'hierarchical'		=> true,
        'taxonomies'        => array('post_tag'),
		//'show_in_menu'		=> false
	);

	register_post_type( 'revista', $args );
	
	//add_submenu_page('edit.php?post_type=article', $args['labels']['name'], $args['labels']['name'], 'edit_posts', 'edit.php?post_type=revista' );
}

add_action( 'init', 'revista_create_post_type' );


// Cria as taxonomias personalizadas
function revista_build_taxonomies() {

	  $labels = array(
	    'name' 				=> 'Publicações',
	    'singular_name'	 	=> 'Publicação',
	    'search_items' 		=> 'Pesquisar publicações',
	    'all_items' 		=> 'Todas as publicações',
	    'edit_item' 		=> 'Editar publicação',
	    'update_item' 		=> 'Atualizar publicação',
	    'add_new_item' 		=> 'Adicionar Nova Publicação',
	    'new_item_name' 	=> 'Nova publicação',
	  );

	  register_taxonomy( 'publicacoes', 'revista', array(
	    'hierarchical'		=> true,
	    'labels' 			=> $labels,
	    'show_ui' 			=> true,
	  	'show_in_nav_menus' => false,
	    'query_var' 		=> true,
	    'rewrite' 			=> array( 'slug' => 'publicacoes' ),
	  ));

}
//add_action( 'init', 'revista_build_taxonomies' );


/*
 * Query
 * Adiciona o post type 'revista' às queries do site
 */
function aspta_post_filter( $query ) {
  global $wp_query;

  if ( !is_preview() && !is_admin() && !is_singular() && !is_404() ) {
    if ($query->is_feed) {
    	$query->set( 'post_type' , array( 'post', 'revista', 'campanha' ) );
    } else {
      $my_post_type = get_query_var( 'post_type' );
      if ( empty( $my_post_type ) )
        $query->set( 'post_type' , 'any' );
    }
  }

  return $query;
}
//add_filter( 'pre_get_posts' , 'aspta_post_filter' );



/*
 * Meta boxes
 *
 * Adiciona novas meta boxes para:
 *
 * 1. Atributos de página: retira o meta box padrão e adiciona um que apenas mostre as páginas mãe
 * 2. Envio de arquivo através do media uploader nativo do WordPress
 *
 */


// Adiciona as meta boxes
add_action( 'add_meta_boxes', 'my_meta_boxes' );

// Salva os dados
add_action( 'save_post', 'my_meta_uploader_save' );

// Gera o media uploader
add_action( 'admin_head', 'my_meta_uploader_script' );



function my_meta_boxes()
{
    // Box para upload
	add_meta_box( 'my_meta_uploader', 'Upload de arquivo', 'my_meta_uploader_setup', 'revista', 'normal', 'high' );

	// Box para definir a página mãe do artigo
//    add_meta_box( 'my_meta_page', 'Página mãe', 'my_meta_page_setup', 'revista', 'side', 'low' );

}


// Adiciona os campos para a meta box da revista
function my_meta_page_setup($post) {
	$post_type_object = get_post_type_object( $post->post_type );

    if ( $post_type_object->hierarchical ) {
        $pages = wp_dropdown_pages( array( 'post_type' => $post->post_type, 'depth' => 1, 'exclude_tree' => $post->ID, 'selected' => $post->post_parent, 'name' => 'parent_id', 'show_option_none' => __( '(no parent)' ), 'sort_column'=> 'menu_order, post_title', 'echo' => 0 ) );
        if ( ! empty($pages) ) { ?>
			<p>Apenas selecione uma página mãe caso esteja cadastrando um artigo. Se estiver cadastrando uma Revista, deixe este campo em branco.</p>
			<label class="screen-reader-text" for="parent_id"><?php _e('Parent') ?></label>
			<?php
			echo $pages;
        }
    }
}


// Adiciona os campos para a meta box de upload
function my_meta_uploader_setup()
{
	global $post;

	$meta = get_post_meta( $post->ID, 'upload_file', true );
	?>

	<p>
		Clique no botão para fazer o upload de um documento. Após o término do upload, clique em <em>Inserir no post</em>.
	</p>
	<p>
		<input id="upload_file" type="text" size="80" name="upload_file" style="width: 85%;" value="<?php if(!empty($meta)) echo $meta; ?>" />
		<input id="upload_file_button" type="button" class="button" value="Fazer upload" />
	</p>

	<?php
}

function my_meta_uploader_save( $post_id ) {

	if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;

	$current_data = get_post_meta( $post_id, 'upload_file', true );

	$new_data = isset($_POST['upload_file'])?$_POST['upload_file']:"";

	if ( $current_data )
	{
		if ( is_null( $new_data ) )
			delete_post_meta( $post_id, 'upload_file' );
		else
			update_post_meta( $post_id, 'upload_file', $new_data );
	}
	elseif ( !is_null( $new_data ) )
	{
		add_post_meta( $post_id, 'upload_file', $new_data, true);
	}

	return $post_id;
}



// Adiciona o script para uma cópia do uploader padrão do WP
function my_meta_uploader_script() { ?>
	<script type="text/javascript">
		jQuery(document).ready(function() {

			var formfield;
			var header_clicked = false;

			jQuery( '#upload_file_button' ).click( function() {
				formfield = jQuery( '#upload_file' ).attr( 'name' );
				tb_show( '', 'media-upload.php?TB_iframe=true' );
				header_clicked = true;

				return false;
			});


			/*
			user inserts file into post. only run custom if user started process using the above process
			window.send_to_editor(html) is how wp would normally handle the received data
			*/

			window.original_send_to_editor = window.send_to_editor;

			// Override send_to_editor function from original script. Writes URL into the textbox. Note: If header is not clicked, we use the original function.
			window.send_to_editor = function( html ) {
				if ( header_clicked ) {
					fileurl = jQuery( html ).attr( 'href' );
					jQuery( '#upload_file' ).val( fileurl );
					header_clicked = false;
					tb_remove();
				}
				else
				{
			  		window.original_send_to_editor( html );
			  	}
			}

		});
  </script>
<?php
}




/*
 * Download do arquivo
 *
 * Adiciona um parágrafo com um link para o arquivo do meta box de upload
 */

function revista_add_pdf( $content ) {

	global $post;

	$meta = get_post_meta( $post->ID, 'upload_file', true );

	if ( is_singular( 'revista' ) && $meta != '' ) {
		$content .= '<p class="download-revista">';
		$content .= '<a href="' . $meta . '" title="Clique para fazer o download do arquivo">';
		$content .= 'Faça o download do arquivo';
		$content .= '</a>';
		$content .= '</p>';
	}
	/*
	 *
	 * O problema se mantem no link de download (signature)
	 *
	 *
	elseif ( is_singular( 'revista' ) && $post->post_parent == 0 ) {
		$documentid = get_post_meta( $post->ID, '_issu_documentid', true );

		if ( $documentid ) {
			$content .= '<p class="download-revista">';
			$content .= '<a href="http://document.issuu.com/';
			$content .= $documentid;
			$content .= '/original.file?AWSAccessKeyId=AKIAJY7E3JMLFKPAGP7A&Expires=1305227539&Signature=wK9eQ4h7PsKZL3VWBmVEk5CoggI%3D';
			$content .= '" title="Clique para fazer o download da revista completa">';
			$content .= 'Faça o download da revista';
			$content .= '</a>';
			$content .= '</p>';
		}

	}


	//http://document.issuu.com/110406203056-24a89312c2f64724879a1c7e5dfd112f/original.file?AWSAccessKeyId=AKIAJY7E3JMLFKPAGP7A&Expires=1305228895&Signature=wK9eQ4h7PsKZL3VWBmVEk5CoggI%3D
	//http://document.issuu.com/110406203056-24a89312c2f64724879a1c7e5dfd112f/original.file?AWSAccessKeyId=AKIAJY7E3JMLFKPAGP7A&Expires=1305227539&Signature=mavOQ2%2B2evxz9pdn4AiF7WyWZvc%3D
	 *
	 */

	return $content;

}
add_filter( 'the_content', 'revista_add_pdf' );


/*
 * Issuu Embed
 *
 * Gera o embed do Issuu sem usar o plugin, o que facilita a definição de campos default para o usuário
 */

function issuu_parser($content)
{
    $content = preg_replace_callback("/\[issuu ([^]]*)\]/i", "issuu_switcher", $content);
    return $content;
}

function getValueWithDefault($regex, $params, $default)
{
    $matchCount = preg_match_all($regex, $params, $matches);
    if ($matchCount) {
        return $matches[1][0];
    } else {
        return $default;
    }
}

function issuu_switcher($matches)
{
    $v = getValueWithDefault('/v=([\S]*)/i', $matches[1], 1);
    switch ($v) {
    case 1:
        return issuu_reader_1($matches);
    case 2:
        return issuu_reader_2($matches);
    default:
        return $matches;
    }
}

function issuu_reader_1($matches)
{
	global $documentid;
        global $post;
    $folderid = getValueWithDefault('/folderid=([\S]*)/i', $matches[1], '');
    $documentid = getValueWithDefault('/documentid=([\S]*)/i', $matches[1], '');
    $username = getValueWithDefault('/username=([\S]*)/i', $matches[1], '');
    $docname = getValueWithDefault('/docname=([\S]*)/i', $matches[1], '');
    //$loadinginfotext = getValueWithDefault('/loadinginfotext=([\S]*)/i', $matches[1], '');
    $tag = getValueWithDefault('/tag=([\S]*)/i', $matches[1], '');
    $showflipbtn = getValueWithDefault('/showflipbtn=([\S]*)/i', $matches[1], 'false');
    $proshowmenu = getValueWithDefault('/proshowmenu=([\S]*)/i', $matches[1], 'false');
    $proshowsidebar = getValueWithDefault('/proshowsidebar=([\S]*)/i', $matches[1], 'false');
    $autoflip = getValueWithDefault('/autoflip=([\S]*)/i', $matches[1], 'false');
    $autofliptime = getValueWithDefault('/autofliptime=([\S]*)/i', $matches[1], 6000);
    //$backgroundcolor = getValueWithDefault('/backgroundcolor=([\S]*)/i', 'FFFFFF', '');
    $layout = getValueWithDefault('/layout=([\S]*)/i', $matches[1], '');
    //$height = getValueWithDefault('/height=([\S]*)/i', $matches[1], 301);
    //$width = getValueWithDefault('/width=([\S]*)/i', $matches[1], 450);
    $unit = 'px';//getValueWithDefault('/unit=([\S]*)/i', $params, 'px');
    $viewmode = getValueWithDefault('/viewmode=([\S]*)/i', $matches[1], '');
    $pagenumber = getValueWithDefault('/pagenumber=([\S]*)/i', $matches[1], 1);
    //$logo = getValueWithDefault('/logo=([\S]*)/i', $matches[1], '');
    //$logooffsetx = getValueWithDefault('/logooffsetx=([\S]*)/i', $matches[1], 0);
    $logooffsety = getValueWithDefault('/logooffsety=([\S]*)/i', $matches[1], 0);
	//$showhtmllink = getValueWithDefault('/showhtmllink=([\S]*)/i', $matches[1], 'false');

    $viewerurl = "http://static.issuu.com/webembed/viewers/style1/v1/IssuuViewer.swf";
    $standaloneurl = "http://issuu.com/$username/docs/$docname?mode=embed";
    $moreurl = "http://issuu.com/search?q=$tag";

    /*
     * Criando os padrões para a Revista Agriculturas
     */
    $loadinginfotext = get_post( $post->ID )->post_title;
    $backgroundcolor = 'FFFFFF';
    $height = 400;
    $width = 610;
    $logo = get_bloginfo( 'stylesheet_directory' ) . '/images/logo-revista.png';
    $logooffsetx = 10;
    $logooffsety = 35;
    $showhtmllink = 'false';

    $flashvars = "mode=embed";
    if ($folderid) {
        // load folder parameters
        $flashvars = "$flashvars&amp;folderId=$folderid";
    } else {
        // load document parameters
        if ($documentid) {
            $flashvars = "$flashvars&amp;documentId=$documentid";
        }
        if ($docname) {
            $flashvars = "$flashvars&amp;docName=$docname";
        }
        if ($username) {
            $flashvars = "$flashvars&amp;username=$username";
        }
        if ($loadinginfotext) {
            $flashvars = "$flashvars&amp;loadingInfoText=$loadinginfotext";
        }
    }
    if ($showflipbtn == "true") {
        $flashvars = "$flashvars&amp;showFlipBtn=true";
    }
    if ($proshowmenu == "true") {
        $flashvars = "$flashvars&amp;proShowMenu=true";
    }
    if ($proshowsidebar == "true") {
        $flashvars = "$flashvars&amp;proShowSidebar=true";
    }
    if ($autoflip == "true") {
        $flashvars = "$flashvars&amp;autoFlip=true";
        if ($autofliptime) {
            $flashvars = "$flashvars&amp;autoFlipTime=$autofliptime";
        }
    }
    if ($backgroundcolor) {
        $flashvars = "$flashvars&amp;backgroundColor=$backgroundcolor";
        $standaloneurl = "$standaloneurl&amp;backgroundColor=$backgroundcolor";
    }
    if ($layout) {
        $flashvars = "$flashvars&amp;layout=$layout";
        $standaloneurl = "$standaloneurl&amp;layout=$layout";
    }
    if ($viewmode) {
        $flashvars = "$flashvars&amp;viewMode=$viewmode";
        $standaloneurl = "$standaloneurl&amp;viewMode=$standaloneurl";
    }
    if ($pagenumber > 1) {
        $flashvars = "$flashvars&amp;pageNumber=$pagenumber";
        $standaloneurl = "$standaloneurl&amp;pageNumbe=$pagenumber";
    }
    if ($logo) {
        $flashvars = "$flashvars&amp;logo=$logo&amp;logoOffsetX=$logooffsetx&amp;logoOffsetY=$logooffsety";
        $standaloneurl = "$standaloneurl&amp;logo=$logo&amp;logoOffsetX=$logooffsetx&amp;logoOffsetY=$logooffsety";
    }

    return ( ($showhtmllink == 'true') ? '<div>' : '') .
           '<object style="width:' . $width . $unit . ';height:' . $height . $unit. '" ><param name="movie" value="' . $viewerurl . '?' . $flashvars . '" />' .
           '<param name="allowfullscreen" value="true"/><param name="menu" value="false"/>' .
           '<embed src="' . $viewerurl . '" type="application/x-shockwave-flash" style="width:' . $width . $unit . ';height:' . $height . $unit . '" flashvars="' .
           $flashvars . '" allowfullscreen="true" menu="false" /></object>' .
           ( ($showhtmllink == 'true') ? ( '<div style="width:' . $width . $unit . ';text-align:left;">' .
           ( $folderid ? '' : ('<a href="' . $standaloneurl . '" target="_blank">Open publication</a> - ') ) .
           'Free <a href="http://issuu.com" target="_blank">publishing</a>' .
           ( $folderid ? '' : ( $tag ? (' - <a href="' . $moreurl. '" target="_blank">More ' . urldecode($tag) . '</a>') : '' ) ) . '</div></div>' ) : '');
}

function issuu_reader_2($matches)
{
    $viewMode = getValueWithDefault('/[\s]+viewMode=([\S]*)/i', $matches[1], 'doublePage');
    $autoFlip = getValueWithDefault('/[\s]+autoFlip=([\S]*)/i', $matches[1], 'false');
    $width = getValueWithDefault('/[\s]+width=([\S]*)/i', $matches[1], 420);
    $height = getValueWithDefault('/[\s]+height=([\S]*)/i', $matches[1], 300);
    $unit = getValueWithDefault('/[\s]+unit=([\S]*)/i', $matches[1], 'px');
    $embedBackground = getValueWithDefault('/[\s]+embedBackground=([\S]*)/i', $matches[1], '');
    $pageNumber = getValueWithDefault('/[\s]+pageNumber=([\S]*)/i', $matches[1], 1);
    $titleBarEnabled = getValueWithDefault('/[\s]+titleBarEnabled=([\S]*)/i', $matches[1], 'false');
    $shareMenuEnabled = getValueWithDefault('/[\s]+shareMenuEnabled=([\S]*)/i', $matches[1], 'true');
    $showHtmlLink = getValueWithDefault('/[\s]+showHtmlLink=([\S]*)/i', $matches[1], 'true');
    $proSidebarEnabled = getValueWithDefault('/[\s]+proSidebarEnabled=([\S]*)/i', $matches[1], 'false');
    // Renamed proShowSidebar to proSidebarEnabled (Feb. 2011)
    if ($proSidebarEnabled == 'false') { // Backward compatible
        $proSidebarEnabled = getValueWithDefault('/[\s]+proShowSidebar=([\S]*)/i', $matches[1], 'false');
    }
    $printButtonEnabled = getValueWithDefault('/[\s]+printButtonEnabled=([\S]*)/i', $matches[1], 'true');
    $shareButtonEnabled = getValueWithDefault('/[\s]+shareButtonEnabled=([\S]*)/i', $matches[1], 'true');
    $searchButtonEnabled = getValueWithDefault('/[\s]+searchButtonEnabled=([\S]*)/i', $matches[1], 'true');
    $linkTarget = getValueWithDefault('/[\s]+linkTarget=([\S]*)/i', $matches[1], '_blank');
    $backgroundColor = getValueWithDefault('/[\s]+backgroundColor=([\S]*)/i', $matches[1], '');
    $theme = getValueWithDefault('/[\s]+theme=([\S]*)/i', $matches[1], 'default');
    $backgroundImage = getValueWithDefault('/[\s]+backgroundImage=([\S]*)/i', $matches[1], '');
    $backgroundStretch = getValueWithDefault('/[\s]+backgroundStretch=([\S]*)/i', $matches[1], 'false');
    $backgroundTile = getValueWithDefault('/[\s]+backgroundTile=([\S]*)/i', $matches[1], 'false');
    $layout = getValueWithDefault('/[\s]+layout=([\S]*)/i', $matches[1], '');
    $logo = getValueWithDefault('/[\s]+logo=([\S]*)/i', $matches[1], '');
    $documentId = getValueWithDefault('/[\s]+documentId=([\S]*)/i', $matches[1], '');
    $name = getValueWithDefault('/[\s]+name=([\S]*)/i', $matches[1], '');
    $username = getValueWithDefault('/[\s]+username=([\S]*)/i', $matches[1], '');
    $tag = getValueWithDefault('/[\s]+tag=([\S]*)/i', $matches[1], '');
    $scriptAccessEnabled = getValueWithDefault('/[\s]+scriptAccessEnabled=([\S]*)/i', $matches[1], 'false');
    $id = getValueWithDefault('/[\s]+id=([\S]*)/i', $matches[1], '');

    $domain = 'issuu.com';

    $readerUrl = 'http://static.' . $domain . '/webembed/viewers/style1/v2/IssuuReader.swf';
    $openUrl = 'http://' . $domain . '/' . $username . '/docs/' . $name . '?mode=embed';
    $moreUrl = 'http://' . $domain . '/search?q=' . $tag;

    $flashVars = 'mode=mini';
    // ****** embed options ******
    // layout
    if ($viewMode == 'doublePage') { // default value
    } else {
        $flashVars = $flashVars . '&amp;viewMode=' . $viewMode;
    }
    if ($autoFlip == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;autoFlip=' . $autoFlip;
    }
    // color
    if ($embedBackground) {
        $flashVars = $flashVars . '&amp;embedBackground=' . $embedBackground;
    }
    // start on
    if ($pageNumber == 1) { // default value
    } else {
        $flashVars = $flashVars . '&amp;pageNumber=' . $pageNumber;
    }
    // show
    if ($titleBarEnabled == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;titleBarEnabled=' . $titleBarEnabled;
    }
    if ($shareMenuEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;shareMenuEnabled=' . $shareMenuEnabled;
    }
    if ($proSidebarEnabled == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;proSidebarEnabled=' . $proSidebarEnabled;
    }
    // ****** fullscreen options ******
    // show
    if ($printButtonEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;printButtonEnabled=' . $printButtonEnabled;
    }
    if ($shareButtonEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;shareButtonEnabled=' . $shareButtonEnabled;
    }
    if ($searchButtonEnabled == 'true') { // default value
    } else {
        $flashVars = $flashVars . '&amp;searchButtonEnabled=' . $searchButtonEnabled;
    }
    // links
    if ($linkTarget == '_blank') { // default value
    } else {
        $flashVars = $flashVars . '&amp;linkTarget=' . $linkTarget;
    }
    // design
    if ($backgroundColor) {
        $flashVars = $flashVars . '&amp;backgroundColor=' . $backgroundColor;
    }
    if ($theme == 'default') { // default value
    } else {
        $flashVars = $flashVars . '&amp;theme=' . $theme;
    }
    if ($backgroundImage) {
        $flashVars = $flashVars . '&amp;backgroundImage=' . $backgroundImage;
    }
    if ($backgroundStretch == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;backgroundStretch=' . $backgroundStretch;
    }
    if ($backgroundTile == 'false') { // default value
    } else {
        $flashVars = $flashVars . '&amp;backgroundTile=' . $backgroundTile;
    }
    if ($layout) {
        $flashVars = $flashVars . '&amp;layout=' . $layout;
    }
    if ($logo) {
        $flashVars = $flashVars . '&amp;logo=' . $logo;
    }
    // ****** document information ******
    if ($documentId) {
        $flashVars = $flashVars . '&amp;documentId=' . $documentId;
    }

    return ( ($showHtmlLink == 'true') ? '<div>' : '' ) .
           '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" style="width:' . $width . $unit . ';height:' . $height . $unit. '" ' .
           ( ($id) ? ('id="' . $id . '" ') : '' ) . '><param name="movie" value="' . $readerUrl . '?' . $flashVars . '" />' .
           '<param name="allowfullscreen" value="true"/>' .
           ( ($linkTarget == '_blank' && $scriptAccessEnabled == 'false') ? '' : '<param name="allowscriptaccess" value="always"/>' ) .
           '<param name="menu" value="false"/><param name="wmode" value="transparent"/>' .
           '<embed src="' . $readerUrl . '" type="application/x-shockwave-flash" style="width:' . $width . $unit . ';height:' . $height . $unit . '" flashvars="' .
           $flashVars . '" allowfullscreen="true" ' .
           ( ($linkTarget == '_blank' && $scriptAccessEnabled == 'false') ? '' : 'allowscriptaccess="always" ' ) .
           'menu="false" wmode="transparent" /></object>' .
           ( ($showHtmlLink == 'true') ? ( '<div style="width:' . $width . $unit . ';text-align:left;">' .
           '<a href="' . $openUrl . '" target="_blank">Open publication</a> - ' .
           'Free <a href="http://' . $domain . '" target="_blank">publishing</a>' .
           '</div>' .
           ( $tag ? (' - <a href="' . $moreUrl. '" target="_blank">More ' . urldecode($tag) . '</a>') : '' ) . '</div></div>' ) : '');
}

add_filter( 'the_content', 'issuu_parser' );


/*
 *
 * Revista - Thumbnail
 *
 * Adiciona um custom field com o id da revista para mostrar automaticamente as capas
 *
 */
function set_revista_thumbnail( $post_id ) {
	global $post;

	$current_data = get_post_meta( $post_id, '_issu_documentid', true );
	if(!is_object($post)) {
		$post = get_post($post_id);
	}
	$content = $post->post_content;
	if( isset($_POST) && array_key_exists('content', $_POST)) {
		$content = $_POST['content'];
	}
	
	// Procura o id da revista
	if( preg_match('/documentid=([\w|-]*)/i', $content, $matches ) && count($matches) > 1 ) {
        $new_data = $matches[1];
    	if ( $current_data )
    	{
    		if ( is_null( $new_data ) )
    			delete_post_meta( $post_id, '_issu_documentid' );
    		else
    			update_post_meta( $post_id, '_issu_documentid', $new_data );
    	}
    	elseif ( ! is_null( $new_data ) )
    	{
    		add_post_meta( $post_id, '_issu_documentid', $new_data, true);
    	}
	}
	else
	{
		if( preg_match('/src=\"([\S]*)\"|src=\\\"([\S]*)\\\"/i', $content, $matches) )
	    {
	        $url = '';
	        if( !empty($matches[1]) )
	        {
	           $url = $matches[1];
	        }
	        elseif( count($matches) > 2 && !empty($matches[2]) )
	        {
	           $url = $matches[2];
	        }
	        else
	        {
	            return false;
	        }
	        
	        if(substr($url, 0, 5) != 'http:')
	        {
	            $url = 'http:'.$url;
	        }
	        
	        $embed_id = substr($url, strripos($url, '/') + 1);
	        try
	        {
	            $file = file_get_contents("https://e.issuu.com/config/{$embed_id}.json");
	        }
	        catch (Exception $e)
	        {
	            return false;
	        }
	        
	        $embed_json = gzdecode($file);
	        if($embed_json !== false)
	        {
    	        $embed_doc = json_decode($embed_json, true);
    	        if($embed_doc)
    	        {
    	            try
    	            {
    	                $file = file_get_contents("https://reader3.isu.pub/{$embed_doc['ownerUsername']}/{$embed_doc['documentURI']}/reader3_4.json");
    	            }
    	            catch (Exception $e)
    	            {
    	                return false;
    	            }
    	            
    	            
    	            $json = gzdecode($file);
    	            if($json !== false)
    	            {
            	        $doc_info = json_decode($json);
            	        if($doc_info)
            	        {
                	        $documentId = "{$doc_info->document->revisionId}-{$doc_info->document->publicationId}";
                	        if ( $current_data )
                	        {
                	            if ( is_null( $documentId ) )
                	                delete_post_meta( $post_id, '_issu_documentid' );
                	                else
                	                    update_post_meta( $post_id, '_issu_documentid', $documentId );
                	        }
                	        elseif ( ! is_null( $documentId ) )
                	        {
                	            add_post_meta( $post_id, '_issu_documentid', $documentId, true);
                	        }
            	        }
    	            }
    	        }
	        }
	    }
	}
}

add_action( 'save_post_revista', 'set_revista_thumbnail' );


/**
 * Mostra o thumbnail da revista
 *
 * @param $size string O tamanho da imagem [large | medium | small]
 */
function the_revista_thumbnail( $size = 'small', $echo = true ) {

	global $post;

	$documentid = get_post_meta( $post->ID, '_issu_documentid', true );
	$imgurl = false;

	if ( $documentid ) {
		if($echo) {
			$imgurl = '<img src="http://image.issuu.com/';
			$imgurl .= $documentid;
			$imgurl .= '/jpg/page_1_thumb_';
			$imgurl .= $size . '.jpg"';
			$imgurl .= ' title="' . get_the_title( $post->ID ) . '"';
			$imgurl .= ' alt="' . get_the_title( $post->ID ) . '"';
			$imgurl .= ' />';
		} else {
			$imgurl = 'http://image.issuu.com/';
			$imgurl .= $documentid;
			$imgurl .= '/jpg/page_1_thumb_';
			$imgurl .= $size . '.jpg';
		}
	}
	else {

		$imgsrc = get_bloginfo( 'stylesheet_directory' ) . '/images/revista-miniatura.png';
		if($echo) {
			$imgurl = '<img src="' . $imgsrc . '" alt="' . get_the_title( $post->ID ) . '" />';
		} else {
			$imgurl = $imgsrc;
		}

	}
	
	if($echo) echo $imgurl;
	
	return $imgurl;
}

function has_resvista_documentid() {
    global $post;
    
    $documentid = get_post_meta( $post->ID, '_issu_documentid', true );
    
    if ( $documentid ) {
        return true;
    }
    return false;
}

function check_revista_documentid() {
	$args = [
			'post_type' => 'revista',
			'nopaging' => true,
			'posts_per_page'=>-1
	];
	$query = new WP_Query($args);
	if($query->have_posts()) {
		global $post;
		while($query->have_posts()) {
			$query->the_post();
			$documentid = get_post_meta( $post->ID, '_issu_documentid', true );
			echo get_the_ID().'\n';
			if(empty( $documentid ) ) {
				set_revista_thumbnail($post->ID);
				$documentid = get_post_meta( $post->ID, '_issu_documentid', true );
				echo "setting document ID to :[$documentid] \n"; 
			}
		}
		wp_reset_postdata();
	}
}

class AsptaNewspaper {
	public static function get_newspaper($id = false, $startPost = true) {
		$newspaper = get_active_issuem_issue();
		return $newspaper;
	}
	
	public static function get_issuem_archive($atts = array()) {
		$issuem_settings = get_issuem_settings();
		$defaults = array(
		'orderby' 		=> 'issue_order',
		'order'			=> 'DESC',
		'limit'			=> 0,
		'pdf_title'		=> $issuem_settings['pdf_title'],
		'default_image'	=> $issuem_settings['default_issue_image'],
		'args'			=> array( 'hide_empty' => 0 ),
		);
		extract( shortcode_atts( $defaults, $atts ) );
		
		if ( is_string( $args ) ) {
			$args = str_replace( '&amp;', '&', $args );
			$args = str_replace( '&#038;', '&', $args );
		}
		
		$args = apply_filters( 'do_issuem_archives_get_terms_args', $args );
		$issuem_issues = get_terms( 'issuem_issue', $args );
		$issues = array();
		$count = 0;
		foreach ( $issuem_issues as $issue ) {
			$issue_meta = get_option( 'issuem_issue_' . $issue->term_id . '_meta' );
			// If issue is not a Draft, add it to the archive array;
			if ( !empty( $issue_meta ) && !empty( $issue_meta['issue_status'] )
			&& ( 'Live' === $issue_meta['issue_status'] || current_user_can( apply_filters( 'see_issuem_draft_issues', 'manage_issues' ) ) ) ) {
				switch( $orderby ) {
					case "issue_order":
						if ( !empty( $issue_meta['issue_order'] ) )
							$issues[ $issue_meta['issue_order'] ] = array('issue' => $issue, 'meta' => $issue_meta);
							else
								$issues[ '-' . ++$count ] = array('issue' => $issue, 'meta' => $issue_meta);
								break;
					case "name":
						$issues[ $issue_meta['name'] ] = array('issue' => $issue, 'meta' => $issue_meta);
						break;
					case "term_id":
						$issues[ $issue->term_id ] = array('issue' => $issue, 'meta' => $issue_meta);
						break;
				}
			} else {
				$issues[ '-' . ++$count ] = array('issue' => $issue, 'meta' => $issue_meta);
			}
		}
		krsort( $issues );
		return $issues;
	}
	
	public static function get_issuem_archive_covers($atts = array(), $size = array(106,150)) {
		$issues = self::get_issuem_archive($atts);
		$issuem_settings = get_issuem_settings();
		$defaults = array(
				'orderby' 		=> 'issue_order',
				'order'			=> 'DESC',
				'limit'			=> 0,
				'pdf_title'		=> $issuem_settings['pdf_title'],
				'default_image'	=> $issuem_settings['default_issue_image'],
				'args'			=> array( 'hide_empty' => 0 ),
		);
		extract( shortcode_atts( $defaults, $atts ) );
		$covers = array();
		foreach($issues as $issue){
			$id = $issue['issue']->term_id;
			//echo '<img src="'.get_site_url().'/wp-content/uploads/2017/06/'.basename(get_attached_file(get_issuem_issue_cover($issue))).'">';
			$cover = wp_get_attachment_image(@get_issuem_issue_cover($id), $size);
			
			if ( empty( $cover ) ) {
				if(is_array($size)) {
					$cover = '<img src="' . $default_image . '" sizes="(max-width: '.$size[0].'px) 100vw, '.$size[0].'px" width="'.$size[0].'" height="'.$size[1].'" />';
				} else {
					$cover = '<img src="' . $default_image . '"  />';
				}
			}
			$article_page = '';
			if ( 0 == $issuem_settings['page_for_articles'] ) {
				$article_page = get_bloginfo( 'wpurl' ) . '/' . apply_filters( 'issuem_page_for_articles', 'article/' );
			} else {
				$article_page = get_page_link( $issuem_settings['page_for_articles'] );
			}
				
			$issue_url = get_term_link( $id, 'issuem_issue' );
			if ( !empty( $issuem_settings['use_issue_tax_links'] ) || is_wp_error( $issue_url ) ) {
				$issue_url = add_query_arg( 'issue', $issue['issue']->slug, $article_page );
			}
			
			if ( !empty( $issue['meta']['pdf_version'] ) || !empty( $issue['meta']['external_pdf_link'] ) ) {
				
				$pdf_url = empty( $issue['meta']['external_pdf_link'] ) ? apply_filters( 'issuem_pdf_attachment_url', wp_get_attachment_url( $issue['meta']['pdf_version'] ), $issue['meta']['pdf_version'] ) : $issue['meta']['external_pdf_link'];
				
				$pdf_line = '<a href="' . $pdf_url . '" target="' . $issuem_settings['pdf_open_target'] . '">';
				
				if ( 'PDF Archive' == $issue['meta']['issue_status'] ) {
					
					$issue_url = $pdf_url;
					$pdf_line .= empty( $pdf_only_title ) ? $issuem_settings['pdf_only_title'] : $pdf_only_title;
					
				} else {
					
					$pdf_line .= empty( $pdf_title ) ? $issuem_settings['pdf_title'] : $pdf_title;
					
				}
				
				$pdf_line .= '</a>';
				
			} else {
				
				$pdf_line = apply_filters( 'issuem_pdf_version', '&nbsp;', $pdf_title, $issue['issue'] );
				
			}
			
			if ( !empty( $issue['meta']['external_link'] ) )
				$issue_url = apply_filters( 'archive_issue_url_external_link', $issue['meta']['external_link'], $issue_url );
				
			$covers[] = array(
					'id' => $issue,
					'cover' => $cover,
					'pdf' => $pdf_line,
					'url' => $issue_url
			);
			
		}
		return $covers;
	}
}

?>
