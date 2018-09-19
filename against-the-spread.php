<?php
require_once 'mypicks_startsession.php';

require_once 'mypicks_def.php';
require_once 'site_fns_diminished.php';
require_once 'mypicks_phpgeneral.php';
$msg = '';


do_header('MySuperPicks.com - Records ATS');
do_nav();
?>
<div class='container'>
<?php
echo_container_breaks();
echoSessionMessage();

echo get_text2('against-the-spread');
?>



</div>
<script src="/js/jquery.tablesort.min.js"></script>
<script>
	$(document).ready(function(){

		//parse the scores
		$(".againstSpread tbody tr td").each(function(index, element){
			let scores = element.innerHTML;
			//console.log(scores);
			let scoreArr = scores.split("-");
			if(scoreArr && scoreArr.length === 3) {
				//console.log(scoreArr);
				let sortOrder = parseFloat(scoreArr[0]) - parseFloat(scoreArr[1]) + (parseFloat(scoreArr[2]) * .5);
				$(element).attr('data-sort-value', sortOrder);
			}
		});

		<?php /* https://github.com/kylefox/jquery-tablesort */ ?>
		$(".againstSpread").tablesort()
            //.data('tablesort').sort($("tr:nth-child(2) th:first"))
	});
</script>
<?php
do_footer('bottom');
?>
