<?php
    require("./connection.php");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Todo list</title>
        <link rel="stylesheet" href="./bootstrap-5.3.1-dist/css/bootstrap.min.css">
        <script type="text/javascript" src="./bootstrap-5.3.1-dist/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="jquery-3.6.0.min.js"></script>
    </head>
    <body class="container">
        <h1>Task</h1>
        <div class="d-flex mt-5">
            <input type="text" class="form-control" placeholder="Task" id="inputTask">
            <button type="button" class="btn btn-primary" id="addTask">Add</button>
        </div>
        <div class="mt-2" id="divContainerData">
            <?php
                $totalData = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `todo`;"))[0];
                $totalPages = ceil($totalData / 2);
                $currentPage = 1;
                $offset = 2;
            ?>
            <table class="table table-hover table-striped table-light">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Task</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody id="tbodyData">
                    <?php
                        $query = mysqli_query($conn, "SELECT * FROM `todo` ORDER BY `todo_id` ASC LIMIT 2;");
                        if(mysqli_num_rows($query)>0){
                            while($rows = mysqli_fetch_assoc($query)){?>
                                <tr id="trDataId<?=$rows['todo_id']?>" class="trData">
                                    <td scope="row"><?php echo $rows['todo_id']?></td>
                                    <td><?php echo $rows['task']?></td>
                                    <td>
                                        <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal" onclick="editTaskCta(<?php echo $rows['todo_id']?>)">Edit</button>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="deleteTaskCta(<?php echo $rows['todo_id']?>)">Delete</button>
                                    </td>
                                </tr><?php
                            }
                        }
                        else{
                            echo "
                                <tr id='noDataFound'>
                                    <td colspan='3'>No data found</td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center" id="divPagination">
                <div class="d-flex justify-content-center align-items-center">
                    <p class="text-dark">Page <?php echo $currentPage?> of <?php echo $totalPages?></p>
                    <p class="text-dark mx-3">|</p>
                    <p class="text-dark">Total <?php echo $totalData?> record/s</p>
                </div>
                <nav aria-label="...">
                    <ul class="pagination">
                        <?php
                            if($currentPage <= 1){?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li><?php
                            }
                            else{?>
                                <li class="page-item">
                                    <a class="page-link" onclick="previousPageCta(event, <?=$currentPage?>, <?=$offset?>)" href="#" tabindex="-1">Previous</a>
                                </li><?php
                            }

                            if($currentPage >= $totalPages){?>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#">Next</a>
                                </li><?php
                            }
                            else{?>
                                <li class="page-item">
                                    <a class="page-link" onclick="nextPageCta(event, <?=$currentPage?>, <?=$offset?>)" href="#">Next</a>
                                </li><?php
                            }

                        ?>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editModalLabel">Edit task</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" class="form-control" id="updateTask" placeholder="Task">
                        <input type="number" id="updateTaskId" class="d-none">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="editCloseModal">Close</button>
                        <button type="button" class="btn btn-success" id="updateTaskCta">Update</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteModalLabel">Delete task</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Deleted task can't be retrieved. Do you want to delete this task?
                        <input type="number" id="deleteTaskId" class="d-none">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="deleteCloseModal">Close</button>
                        <button type="button" class="btn btn-danger" id="deleteTaskCta">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-secondary">
                    <img src="" class="rounded me-2" alt="">
                    <strong class="me-auto text-light">Notification</strong>
                    <small class="text-light">Now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body bg-dark text-light" id="toastBody">
                    Hello, world! This is a toast message.
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function(){
                $('#inputTask').on('input', async ()=>{
                    $('#inputTask').removeClass("border border-danger");
                });
                $("#addTask").on("click", async()=>{
                    if($.trim($('#inputTask').val()).length > 0){
                        $.post("request.php",{addTask : $('#inputTask').val()}, async (data, status)=>{
                            if(status === 'success'){
                                if($('.trData').length < 2){
                                    $('#tbodyData').append(data);
                                }
                                $('#inputTask').val('');
                                $("#noDataFound").addClass("d-none");

                                const toastLiveExample = document.getElementById('liveToast');
                                const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
                                $('#toastBody').html('Task added');
                                toastBootstrap.show();

                                $.get('request.php', {divPagination : ''}, async (data, status) =>{
                                    $('#divPagination').html(data);
                                });
                            }
                        });
                    }
                    else{
                        $('#inputTask').addClass("border border-danger");
                    }
                });

                editTaskCta = async (todoId)=>{
                    $.get("request.php",{editTodoId : todoId}, async (data, status)=>{
                        if(status === 'success'){
                            $('#updateTask').val(data);
                            $('#updateTaskId').val(todoId);
                        }
                    });
                }
                $('#updateTask').on('input', async ()=>{
                    $('#updateTask').removeClass("border border-danger");
                });
                $('#updateTaskCta').on('click', async ()=>{

                    if($.trim($('#updateTask').val()).length > 0){
                        const task = $('#updateTask').val();
                        const todoId = $('#updateTaskId').val();

                        $.post("request.php",{updateTask : task, updateTodoId : todoId}, async (data, status)=>{
                            if(status === 'success'){
                                $('#updateTask').val('');
                                $('#editCloseModal').click();
                                $(`#trDataId${todoId}`).html(data);

                                const toastLiveExample = document.getElementById('liveToast');
                                const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
                                $('#toastBody').html('Task updated');
                                toastBootstrap.show();
                            }
                        });

                    }
                    else{
                        $('#updateTask').addClass("border border-danger");
                    }
                });

                deleteTaskCta = async (todoId)=>{
                    $('#deleteTaskId').val(todoId);
                }
                $('#deleteTaskCta').on('click', async ()=>{
                    $.get("request.php",{deleteTodoId : $('#deleteTaskId').val()}, async (data, status)=>{
                        if(status === 'success'){
                            $('#deleteCloseModal').click();
                            $(`#trDataId${data}`).addClass('d-none');

                            const toastLiveExample = document.getElementById('liveToast');
                            const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample)
                            $('#toastBody').html('Task deleted');
                            toastBootstrap.show();

                            $.get('request.php', {deleteTask : ''}, async (data, status)=>{
                                if(status === 'success'){
                                    if(data !== 'false'){
                                        $('#tbodyData').html(data);
                                    }
                                }
                            });
                        }
                    });
                });

                //previous and next pages
                previousPageCta = async (e, currentPage, offset)=>{
                    e.preventDefault();
                    $.get("request.php",{currentPage : currentPage, offset : offset, buttonPage : 'previous'}, async (data, status)=>{
                        if(status === "success"){
                            $('#divContainerData').html(data);
                        }
                    });
                }
                nextPageCta = async (e, currentPage, offset)=>{
                    e.preventDefault();
                    $.get("request.php",{currentPage : currentPage, offset : offset, buttonPage : 'next'}, async (data, status)=>{
                        if(status === "success"){
                            $('#divContainerData').html(data);
                        }
                    });
                }
            });
        </script>
    </body>
</html>