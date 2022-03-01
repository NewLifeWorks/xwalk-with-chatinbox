<?php 
 
require ("includes/header.php");
 
$message_obj = new Message($con, $userLoggedIn);
 
 
if(isset($_GET['profile_username'])) {
 
	$username = $_GET['profile_username'];
       $opened_query = mysqli_query($con, "UPDATE notifications SET opened='yes' WHERE user_to='$userLoggedIn' AND link='$username'");
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
	$user_array = mysqli_fetch_array($user_details_query);
	$num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}
 
$friend_to_remove = new User($con, $username);
$friend_name = $friend_to_remove->getFirstAndLastName();
 
 
if(isset($_POST['add_friend'])) {
 
	$user = new User($con, $userLoggedIn);
	$user->sendRequest($username);
}
 
if(isset($_POST['respond_request'])) {
 
	header("Location: requests.php");
}
 
?>
 
<style type="text/css">
	
	.wrapper {
 
		margin-left: 0px;
		padding-left: 0px;
	}
 
 
</style>
 
<div class="profile_left">
	<img src="<?php echo $user_array['profile_pic']; ?>">
 
	<div class="profile_info">
	
		<p><?php echo "Posts: " . $user_array['num_posts']; ?></p>
		<p><?php echo "Likes: " . $user_array['num_likes']; ?></p>
		<p><?php echo "Friends: " . $num_friends; ?></p>
 
	</div>
 
	<form action="<?php echo $username; ?>" method="POST">
		
		<?php 
 
		$profile_user_obj = new User($con, $username);
 
		if($profile_user_obj->isClosed()) {
 
			header("Location: user_closed.php");
		}
 
		$logged_in_user_obj = new User($con, $userLoggedIn);
 
		if($userLoggedIn != $username) { 
			if($logged_in_user_obj->isFriend($username)) 
				echo '<input type="submit" id="remove" name="remove_friend" class="danger" value="Remove Friend"><br>';
			else if($logged_in_user_obj->didReceiveRequest($username))
				echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
			else if($logged_in_user_obj->didSendRequest($username))
				echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
			else
				echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';
 
		}
 
		?>
 
		
	</form>
 
  <?php 
 
  if($logged_in_user_obj->isFriend($username)) 
     echo '<input type="submit" class="deep_blue" data-toggle="modal" data-target="#post_form" value="Post Something">';
 
  ?>
 
</div>
 
<div class="profile_main_column column">
    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
      <li class="nav-item">
        <a href="#newsfeed_div" class="nav-link active" id="home-tab" data-toggle="tab" href="#newsfeed" role="tab" aria-controls="newsfeed" aria-selected="true">Newsfeed</a>
      </li>
      <li class="nav-item">
        <a href="#messages_div" class="nav-link" id="profile-tab" data-toggle="tab" href="#messages" role="tab" aria-controls="messages" aria-selected="false">Messages</a>
      </li>
    </ul>
 
    <div class="tab-content" id="myTabContent">
          <div class="tab-pane fade show active" id="newsfeed_div" role="tabpanel" aria-labelledby="newsfeed-tab">
            <div class="posts_area"></div>
            <img id="loading" src="assets/images/icons/loading.gif">
         </div>
 
          <div role="tabpanel" class="tab-pane" id="messages_div">
            
              <?php  
              
                echo "<h4>You and " . $profile_user_obj->getFirstAndLastName() . "</h4><hr><br>";
 
                echo "<div class='loaded_messages' id='scroll_messages'>";
                  echo $message_obj->getMessages($username);
                echo "</div>";
              ?>
 
            <div class="message_post">
 
              <form action="" method="POST" enctype="multipart/form-data" name="imgForm">
              
                  <textarea name='message_body' id='message_textarea' placeholder='Write your message...'></textarea>
                  <input type='submit' onclick="sendMessage()" name='post_message' class='info' id='message_submit' value='Send'>
                  <input type='file' name='fileToUpload' id='fileToUpload'>
 
              </form> 
 
            </div>
 
            <script>
              
             $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                var div = document.getElementById("scroll_messages");
       
                if(div != null) {
                  div.scrollTop = div.scrollHeight;
                }
 
              });
 
            </script>
       
          </div>
    </div>
</div>
 
<!-- Modal -->
<div class="modal fade" id="post_form" tabindex="-1" role="dialog" aria-labelledby="postModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
 
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Post something</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
 
      <div class="modal-body">
        <p>This will appear on the user's profile page and also on their newsfeed for your friends to see</p>
        <form class="profile_post" action="" method="POST">
        	
        	<div class="form-group">
        		
        		<textarea class="form-control" name="post_body"></textarea>
        		<input type="hidden" name="user_from" value="<?php echo $userLoggedIn;?>">
        		<input type="hidden" name="user_to" value="<?php echo $username;?>">
 
        	</div>
 
        </form>
      </div>
 
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" name="post_button" id="submit_profile_post">Post</button>
      </div>
    </div>
  </div>
</div>
 
<script>
 
  var userTo = "<?php echo $username; ?>";
 
  const sendMessage = () => {
 
    var body = $("textarea").val();
 
    var form = document.forms.namedItem("imgForm");
 
    var formData = new FormData(form);
 
    var otherData = [];
 
    otherData.push({"me":userLoggedIn, "body":body, "friend":userTo});
 
    formData.append('otherData', JSON.stringify(otherData));
 
    $.ajax({
          type: "POST",
          url: "includes/handlers/send_message.php",
          data: formData,
          contentType: false,
          processData: false,   
          success:(function(data) {
          
            $("textarea").val("");
            $(".checkSeen").remove();
        })
    });
 
    const scrollDown = () => {
 
      div.scrollTop = div.scrollHeight;
    }
 
    setTimeout(scrollDown, 800);
 
    var file = document.getElementById("fileToUpload"); 
    file.value = file.defaultValue;
 
  }
 
  const getMessages = () => {
 
    $.post("includes/handlers/get_messages.php", {me:userLoggedIn, friend:userTo}, function(result){
 
      $(".loaded_messages").append(result);
 
        var all_elements = $(".loaded_messages").children();
 
        all_elements.each(function(){
          var el_id = this.id;
          
          // data("verified") prevents the removal triggered by its duplicate, if any.
          $(this).data("verified",true);
 
          all_elements.each(function(){
            if(el_id==this.id && !$(this).data("verified")){
              $(this).remove();
            }
          });
        });
        
        // Turn all "surviving" element's data("verified") to false for future "clean".
        $(".loaded_messages").children().each(function(){
          $(this).data("verified",false);
        });
    
    });
  }
 
  setInterval(getMessages, 500);
 
  const checkSeen = () => {
 
    $.post("includes/handlers/check_seen.php", {me:userLoggedIn, friend:userTo}, function(data){
 
      $(".checkSeen").html(data);
      
    });
  }
 
  setInterval(checkSeen, 4000);
  
 
  $(function(){
  
    $(document).keypress(function(e){
 
      if(e.keyCode === 13 && e.shiftKey === false && $("#message_textarea").is(":focus")) {
 
        e.preventDefault();
 
        $("#message_submit").click();
 
        const scrollDown = () => {
    
          var div = document.getElementById("scroll_messages");
 
          if(div != null) {
            div.scrollTop = div.scrollHeight;
          }
 
        }
 
        setTimeout(scrollDown, 800); 
 
      }
 
    });
 
    $("#messages_div").submit(function(e) {
        e.preventDefault();
    });
 
  });
 
  $(function(){
 
    const user = '<?php echo $username; ?>';
    const userLoggedIn = '<?php echo $userLoggedIn; ?>';
    const fullName = '<?php echo $friend_name; ?>';
 
      $("#remove").click(function(){
 
              bootbox.confirm("Are you sure you want to remove " + fullName + " from your friends?", function(result) {
 
                  if(result) {
                      $.post("includes/handlers/delete_friend.php", {username:user, userLoggedIn:userLoggedIn});
                      location.reload();
                  }
              });
 
          return false;   
      });
  });
 
   $(function(){
       var userLoggedIn = '<?php echo $userLoggedIn; ?>';
       var profileUsername = '<?php echo $username; ?>';
       var inProgress = false;
       loadPosts(); //Load first posts
       $(window).scroll(function() {
           var bottomElement = $(".status_post").last();
           var noMorePosts = $('.posts_area').find('.noMorePosts').val();
           // isElementInViewport uses getBoundingClientRect(), which requires the HTML DOM object, not the jQuery object. The jQuery equivalent is using [0] as shown below.
           if (isElementInView(bottomElement[0]) && noMorePosts == 'false') {
               loadPosts();
           }
       });
       function loadPosts() {
           if(inProgress) { //If it is already in the process of loading some posts, just return
               return;
           }
          
           inProgress = true;
           $('#loading').show();
           var page = $('.posts_area').find('.nextPage').val() || 1; //If .nextPage couldn't be found, it must not be on the page yet (it must be the first time loading posts), so use the value '1'
           $.ajax({
               url: "includes/handlers/ajax_load_profile_posts.php",
               type: "POST",
               data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
               cache:false,
               success: function(response) {
                   $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage
                   $('.posts_area').find('.noMorePosts').remove(); 
                   $('.posts_area').find('.noMorePostsText').remove();
                   $('#loading').hide();
                   $(".posts_area").append(response);
                   inProgress = false;
               }
           });
       }
       //Check if the element is in view
       function isElementInView (el) {
             if(el == null) {
                return;
            }
           var rect = el.getBoundingClientRect();
           return (
               rect.top >= 0 &&
               rect.left >= 0 &&
               rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && //* or $(window).height()
               rect.right <= (window.innerWidth || document.documentElement.clientWidth) //* or $(window).width()
           );
       }
   });
 
   </script>
 
</div>
 
</body>
</html>
