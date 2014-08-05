<html>
<head>

 <!-- Le styles -->
 <link href="<?php echo static_files_url(); ?>css/bootstrap.css" rel="stylesheet">

</head>

<body>
    <div id="result_table">
        <script id="result_template" type="text/x-handlebars-template">
         <table class="table table-striped table-bordered">

                     <tbody>

                     <!-- mustache templates -->

                     {{! only output if result exists }}

                      {{#if results}}
                         {{#each results}}

                         <tr>
                             <td>{{username}}</td>
                             <td>{{email}}</td>
                             <td>{{password}}</td>
                         </tr>
                         {{/each}}
                     {{else}}
                          <tr><td colspan="3">No records found!</tr></td>
                     {{/if}}
                     </tbody>
                  </table>
        </script>
    </div>

    <div id="pagination"></div>
<!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo static_files_url(); ?>js/jquery.js"></script>
     <script type="text/javascript">
    $(document).ready(function() {

    var source = $("#result_table").html();
    if (source) {

    var result_template = Handlebars.compile(source);

    function load_result(index) {
        index = index || 0;
        $.post(base_url + "home/index/" + index, {  ajax: true }, function(data) {
        $("#result_table").html(result_template({results: data.results}));
    // pagination
    $('#pagination').html(data.pagination);
    }, "json");
 }

 //calling the function
 load_result();
}


 //pagination update
 $('#pagination').on('click', '.page_test a', function(e) {
  e.preventDefault();
  //grab the last paramter from url
  var link = $(this).attr("href").split(/\//g).pop();
  load_result(link);
  return false;
 });
});
 </script>
    <script>
     var base_url = "<?php echo current_base_url(); ?>";
    </script>
    <script src="<?php echo static_files_url(); ?>js/bootstrap.js"></script>
    <script src="<?php echo static_files_url(); ?>js/handlebars.js"></script>

</html>