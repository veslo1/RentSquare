<br>
<?php echo $this->Html->link('<i class="icon-plus icon-white"></i> Submit New', '#addMaintModal', array('class' => 'btn btn-success new_mt_req','escape'=>false,'role'=>'button','data-toggle'=>'modal')); ?>
<div class="page_title">
  <h1>Maintenance Requests</h1>
</div>

<div class="clear"><br></div>
<div class="block_title messages_block_title">
  Overview
</div>


<div id='maintenance-overview' class='table messages'>
	<table class="table-striped format subheader">
		<tr class="no_click">
			<th width="70%"><?php echo $this->Paginator->sort('MaintenanceTicket.title','Title');?> <span class="caret"></span></th>
		  <th><?php echo $this->Paginator->sort('MaintenanceTicket.created','Date');?> <span class="caret"></span></th>
			<th><?php echo $this->Paginator->sort('MaintenanceTicket.status','Status');?> <span class="caret"></span></th>
	</tr>
	
		<?php
		  if(isset($tickets) && count($tickets) > 0):
			foreach($tickets as $idx => $ticket){
			  if(strlen($ticket['MaintenanceTicket']['title']) > 190){
  			  $subject = substr($ticket['MaintenanceTicket']['title'], 0, 190) . '...';
			  } else {
  			  $subject = $ticket['MaintenanceTicket']['title'];
			  }
				$link = $this->Html->link($subject, array('controller' => 'MaintenanceTickets', 'action' => 'view', $ticket['MaintenanceTicket']['id']), array('class' => 'msg-link'));

				$unitNum = '(Unassigned)';
				if(isset($ticket['Tenant']['Unit']['number']))
					$unitNum = $ticket['Tenant']['Unit']['number'];

				$odd = $idx % 2;

				$status = $ticket['MaintenanceTicket']['status'];

				echo $this->Html->tableCells(array($link, $this->Time2->messageTime($this->Time->convert(strtotime($ticket['MaintenanceTicket']['created']), $user_timezone)), array($status, array('class' => $status.' status'))), array('class' => 'odd'), array('class' => 'even'));
			}
			else:
			  echo '<tr class="no_click"><td colspan="5" class="no_rows">
			  <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" width="80px" height="80px" viewBox="0 0 100 100" enable-background="new 0 0 100 100" xml:space="preserve">
        <g>
        	<path fill="#ddd" d="M58.637,52.478c-0.465-0.753-1.524-1.106-2.349-0.787l-3.651,1.402c-0.826,0.317-1.988,0.044-2.591-0.608L47.3,49.892   c-0.688-0.558-1.03-1.704-0.76-2.548l1.188-3.725c0.268-0.842-0.164-1.847-0.958-2.234l-7.375-2.806   c-0.852-0.241-1.842,0.225-2.203,1.033l-1.588,3.574c-0.359,0.807-1.375,1.437-2.261,1.396l-3.777,0.115   c-0.878,0.087-1.932-0.483-2.339-1.269l-1.795-3.475c-0.404-0.787-1.431-1.225-2.279-0.975c0,0-1.993,0.589-3.719,1.364   c-1.729,0.775-3.493,1.873-3.493,1.873c-0.752,0.468-1.106,1.525-0.791,2.351l1.406,3.652c0.318,0.826,0.045,1.991-0.606,2.59   l-2.596,2.743c-0.557,0.688-1.706,1.031-2.547,0.762l-3.725-1.189c-0.842-0.268-1.847,0.162-2.235,0.96l-2.806,7.375   C1.802,62.31,2.267,63.3,3.074,63.66l3.574,1.587c0.808,0.359,1.438,1.378,1.397,2.261l0.114,3.776   c0.089,0.88-0.482,1.934-1.268,2.34L3.415,75.42c-0.785,0.404-1.223,1.432-0.974,2.281c0,0,0.59,1.99,1.365,3.716   c0.774,1.729,1.874,3.495,1.874,3.495c0.467,0.751,1.524,1.106,2.35,0.787l3.651-1.402c0.827-0.319,1.993-0.044,2.591,0.606   l2.747,2.595c0.687,0.555,1.028,1.703,0.76,2.546l-1.188,3.725c-0.27,0.844,0.162,1.85,0.958,2.236l7.375,2.808   c0.853,0.239,1.843-0.228,2.202-1.035l1.587-3.573c0.36-0.809,1.378-1.439,2.262-1.398l3.776-0.113   c0.881-0.09,1.933,0.483,2.339,1.27l1.793,3.478c0.407,0.785,1.434,1.225,2.282,0.973c0,0,1.99-0.591,3.719-1.365   c1.728-0.777,3.493-1.873,3.493-1.873c0.751-0.468,1.106-1.524,0.789-2.353l-1.403-3.647c-0.319-0.828-0.045-1.993,0.605-2.592   l2.596-2.748c0.561-0.685,1.702-1.028,2.546-0.759l3.725,1.189c0.844,0.269,1.85-0.162,2.239-0.957l2.803-7.376   c0.24-0.851-0.224-1.843-1.034-2.203l-3.571-1.585c-0.809-0.363-1.438-1.38-1.399-2.265l-0.112-3.773   c-0.088-0.883,0.483-1.937,1.27-2.34l3.475-1.795c0.788-0.404,1.226-1.435,0.976-2.28c0,0-0.59-1.994-1.366-3.722   C59.736,54.241,58.637,52.478,58.637,52.478z M35.39,75.895c-3.978,1.786-8.649,0.008-10.433-3.969   c-1.786-3.977-0.008-8.649,3.97-10.433c3.976-1.783,8.646-0.008,10.431,3.971C41.144,69.438,39.367,74.11,35.39,75.895z"></path>
        	<path fill="#ddd" d="M97.509,25.016c-0.108-0.762-0.822-1.417-1.591-1.455l-3.396-0.168c-0.771-0.038-1.602-0.666-1.848-1.395l-1.269-3.029   c-0.351-0.686-0.22-1.715,0.29-2.291l2.257-2.542c0.511-0.576,0.522-1.526,0.024-2.114l-4.875-4.825   c-0.592-0.491-1.544-0.469-2.112,0.047l-2.521,2.281c-0.568,0.516-1.599,0.657-2.287,0.314L77.14,8.601   c-0.731-0.239-1.365-1.063-1.411-1.833l-0.204-3.392c-0.047-0.769-0.707-1.478-1.471-1.576c0,0-1.791-0.234-3.438-0.225   c-1.646,0.008-3.435,0.261-3.435,0.261c-0.758,0.105-1.414,0.822-1.454,1.59L65.561,6.82c-0.038,0.771-0.667,1.6-1.394,1.847   l-3.03,1.27c-0.687,0.351-1.717,0.219-2.292-0.292L56.304,7.39c-0.574-0.511-1.527-0.52-2.115-0.025l-4.825,4.874   c-0.488,0.592-0.468,1.546,0.047,2.115l2.279,2.518c0.519,0.571,0.66,1.602,0.317,2.288l-1.239,3.044   c-0.238,0.729-1.065,1.366-1.831,1.412l-3.395,0.202c-0.766,0.047-1.476,0.709-1.574,1.471c0,0-0.234,1.792-0.225,3.437   c0.008,1.646,0.26,3.435,0.26,3.435c0.106,0.761,0.821,1.416,1.59,1.454l3.396,0.168c0.768,0.038,1.599,0.666,1.848,1.394   l1.266,3.029c0.35,0.684,0.22,1.716-0.29,2.291l-2.257,2.542c-0.51,0.576-0.52,1.527-0.025,2.115l4.876,4.824   c0.593,0.491,1.544,0.47,2.113-0.046l2.521-2.28c0.568-0.517,1.598-0.658,2.286-0.316l3.042,1.238   c0.732,0.24,1.366,1.064,1.414,1.833l0.203,3.392c0.046,0.77,0.707,1.477,1.47,1.577c0,0,1.79,0.231,3.437,0.224   c1.646-0.007,3.435-0.26,3.435-0.26c0.762-0.105,1.418-0.822,1.453-1.593l0.17-3.392c0.037-0.769,0.665-1.6,1.394-1.847   l3.029-1.269c0.684-0.349,1.717-0.22,2.291,0.291l2.544,2.255c0.574,0.511,1.525,0.522,2.113,0.025l4.824-4.875   c0.491-0.592,0.471-1.542-0.047-2.112l-2.279-2.52c-0.518-0.571-0.658-1.601-0.316-2.288l1.237-3.043   c0.239-0.73,1.065-1.366,1.83-1.411l3.397-0.205c0.767-0.045,1.476-0.708,1.575-1.471c0,0,0.231-1.79,0.224-3.437   C97.761,26.804,97.509,25.016,97.509,25.016z M70.795,36.412c-4.321,0.023-7.843-3.463-7.865-7.785   c-0.022-4.321,3.463-7.845,7.785-7.866c4.323-0.022,7.844,3.464,7.866,7.786C78.603,32.87,75.117,36.392,70.795,36.412z"></path>
        </g>
        </svg><br><br>
        Currently no maintenance tickets open.</td></tr>';
			endif;
		?>
	</table>
</div>
	<div class="clear"></div>
	<div class='right'><?php echo $this->element('paging'); ?></div>
	
	<!-- Add Maint Modal -->
<div id="addMaintModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="addMainLabel" aria-hidden="true">
  <?php 
		echo $this->Form->create('MaintenanceTicket', array('controller' => 'MaintenanceTickets', 'action' => 'create','enctype' => 'multipart/form-data'));
  ?>
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
    <h3 id="addMainLabel">New Maintenance Request</h3>
  </div>
  <div class="modal-body">
    <div id='MaintenanceTicketDescription' class=''>
    	Please fill in the fields below: <br><br>
    	<div class='widget-pad'>
    	<?php
    		echo $this->Form->input('title', array('label' => 'Subject','style'=>'width: 100%','class'=>'validate[required]'));
                echo "<div><span style=\"width: 50%;\">Nature</span><span style=\"width: 50%; float: left;\">Location</span></div>";
    		echo $this->Form->input('location', array('label' => false, 'div'=>false, 'style' => 'width: 48%', 'type' => 'select', 'options' => Configure::read('RS.Tickets.Locations'), 'class'=>'validate[required]'));
                echo "<span style=\"width: 10px;display: inline-block;\">&nbsp;</span>";
    		echo $this->Form->input('nature', array('label' => false, 'div'=>false, 'style' => 'width: 48%', 'type' => 'select', 'options' => Configure::read('RS.Tickets.Nature'),'class'=>'validate[required]'));
        ?>
    		<label for="MaintenanceTicketNature">Permission to Enter?</label>
        <?php echo '<ul class="permission_to_enter"><li>'.$this->Form->input('can_enter', array('type'=>'radio','options'=>array('Yes','No'),'legend'=>false,'div'=>false,'class'=>'validate[required]','value'=>'0','separator'=>'</li><li>')) .'</li></ul>';
        echo '<div class="clear"><br></div>';
    		echo $this->Form->input('description', array('label' => 'Description', 'type' => 'textarea'));
        echo '<input type="hidden" name="MAX_FILE_SIZE" value="32000000" />';
    		echo $this->Form->input('image_file',array('type' => 'file','label'=>'Upload a Photo')); 
    		echo $this->Form->input('image_file_2',array('type' => 'file','label'=>'Photo 2')); 
    		echo $this->Form->input('image_file_3',array('type' => 'file','label'=>'Photo 3')); 
    		echo $this->Form->input('image_file_4',array('type' => 'file','label'=>'Photo 4'));  
    	?>
    	<div class='clear'></div>
    	</div>
    </div>
  </div>
  <div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <?php echo $this->Form->button("Submit", array('class' => 'btn btn-success','escape' => false )); ?>

  </div>
  <?php  echo $this->Form->end(); ?>
</div>
