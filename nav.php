<nav class="navbar navbar-expand-lg nav">
  <div class="container-fluid">

    <a class="navbar-brand text-white" href="index.php">TaskBot</a>

    <button class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">

      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-4">

        <?php if (!isset($_SESSION['username'])): ?>

            <li class="nav-item">
              <a class="nav-link text-white" href="index.php">Home</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="#">FAQ</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="#">Accessibility</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="#">Contact</a>
            </li>

        <?php else: ?>

            <?php if ($_SESSION['role'] === 'Staff'): ?>

                <li class="nav-item">
                  <a class="nav-link text-white" href="index.php?page=tasks">My Tasks</a>
                </li>

                <li class="nav-item">
                  <a class="nav-link text-white" href="index.php?page=lists">Task List</a>
                </li>
    
                <li class="nav-item">
                  <a class="nav-link text-white" href="index.php?page=addtask">Add Task</a>
                </li>
                             <li class="nav-item">
                  <a class="nav-link text-white" href="accessibility.php?page=alltasks">Accessibility</a>
                </li>

                <li class="nav-item">
                  <a class="nav-link text-white" href="logout.php">Logout</a>
                </li>

            <?php elseif ($_SESSION['role'] === 'Admin'): ?>

                <li class="nav-item">
                  <a class="nav-link text-white" href="index.php?page=alltasks">All Tasks</a>
                </li>
          
                <li class="nav-item">
                  <a class="nav-link text-white" href="index.php?page=manageusers">Manage Users</a>
                </li>
                  <li class="nav-item">
                  <a class="nav-link text-white" href="accessibility.php?page=alltasks">Accessibility</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white" href="logout.php">Logout</a>
                </li>

            <?php endif; ?>

        <?php endif; ?>

      </ul>
    </div>
  </div>
</nav>