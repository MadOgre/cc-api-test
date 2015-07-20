<?php
	/*echo 
		'<div class="modal-header">
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
                 <h4 class="modal-title">SUCCESS!</h4>
		</div>
        <div class="modal-body">You have successfully added a contact</div>';*/
	echo '<script>
			var $ = parent.$;
			$(parent.document).find("#addmodal .modal-title").html("Success");
			$(parent.document).find("#addmodal .modal-body").html("You have successfully added a contact");
		 </script>';
?>
	