<?php
require("./connection.php");

if(isset($_POST['addTask'])){
    $task = mysqli_real_escape_string($conn, $_POST['addTask']);
    mysqli_query($conn, "INSERT INTO `todo`(`task`) VALUES('$task');");

    $query = mysqli_query($conn, "SELECT * FROM `todo` ORDER BY `todo_id` DESC LIMIT 1;");
    if(mysqli_num_rows($query)>0){
        $rows = mysqli_fetch_assoc($query);
        $todoId = $rows['todo_id'];
        $task = $rows['task'];
        echo "
            <tr id='trDataId".$todoId."' class='trData'>
                <td>$todoId</td>
                <td>$task</td>
                <td>
                    <button type='button' class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#editModal' onclick='editTaskCta($todoId)'>Edit</button>
                    <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal' onclick='deleteTaskCta($todoId)'>Delete</button>
                </td>
            </tr>
        ";
    }
}

if(isset($_GET['editTodoId'])){
    $query = mysqli_query($conn, "SELECT * FROM `todo` WHERE `todo_id`=".$_GET['editTodoId']);
    if(mysqli_num_rows($query)>0){
        $rows = mysqli_fetch_assoc($query);
        echo $rows['task'];
    }
}

if(isset($_POST['updateTask']) && isset($_POST['updateTodoId'])){
    $task = mysqli_real_escape_string($conn, $_POST['updateTask']);
    $todoId = $_POST['updateTodoId'];

    mysqli_query($conn, "UPDATE `todo` SET `task`='$task' WHERE `todo_id`=$todoId;");

    $query = mysqli_query($conn, "SELECT * FROM `todo` WHERE `todo_id`=$todoId;");
    if(mysqli_num_rows($query)>0){
        $rows = mysqli_fetch_assoc($query);
        $todoId = $rows['todo_id'];
        $task = $rows['task'];
        echo "
            <td>$todoId</td>
            <td>$task</td>
            <td>
                <button type='button' class='btn btn-warning' data-bs-toggle='modal' data-bs-target='#editModal' onclick='editTaskCta($todoId)'>Edit</button>
                <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal' onclick='deleteTaskCta($todoId)'>Delete</button>
            </td>
        ";
    }
}

if(isset($_GET['deleteTodoId'])){
    $todoId = $_GET['deleteTodoId'];

    mysqli_query($conn, "DELETE FROM `todo` WHERE `todo_id`=$todoId;");
    echo $todoId;
}

if(isset($_GET['deleteTask'])){
    $totalData = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `todo`;"))[0];
    
    if($totalData <= 0){
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
    }
    else{
        echo 'false';
    }
}


if(isset($_GET['currentPage']) && isset($_GET['offset']) && isset($_GET['buttonPage'])){
    $currentPage = $_GET['currentPage'];
    $offset = $_GET['offset'];
    $buttonPage = $_GET['buttonPage'];

    $totalData = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `todo`;"))[0];
    $totalPages = ceil($totalData / 2);

    if($buttonPage === 'previous'){
        $currentPage =  $currentPage - 1;
    }
    elseif($buttonPage === 'next'){
        $currentPage = $currentPage + 1;
    }?>

    
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
                $query = mysqli_query($conn, "SELECT * FROM `todo` ORDER BY `todo_id` ASC LIMIT 2 OFFSET $offset;");
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
    <div class="d-flex justify-content-between align-items-center">
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
                            <a class="page-link" onclick="previousPageCta(event, <?=$currentPage?>, <?=$offset-2?>)" href="#" tabindex="-1">Previous</a>
                        </li><?php
                    }

                    if($currentPage >= $totalPages){?>
                        <li class="page-item disabled">
                            <a class="page-link" href="#">Next</a>
                        </li><?php
                    }
                    else{?>
                        <li class="page-item">
                            <a class="page-link" onclick="nextPageCta(event, <?=$currentPage?>, <?=$offset+2?>)" href="#">Next</a>
                        </li><?php
                    }

                ?>
            </ul>
        </nav>
    </div><?php
}

if(isset($_GET['divPagination'])){
    $totalData = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `todo`;"))[0];
    $totalPages = ceil($totalData / 2);
    $currentPage = 1;
    $offset = 2;?>

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
    </nav><?php
}