<nav class="navbar navbar-expand-lg nav">
  <div class="container-fluid">

    <!-- Brand -->
    <a class="navbar-brand text-white" href="index.php">
      TaskBot
    </a>

    <!-- Hamburger -->
    <button class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation">

      <span class="navbar-toggler-icon"></span>

    </button>

    <!-- Collapsible menu -->
    <div class="collapse navbar-collapse" id="navbarNav">

      <!-- Nav links -->
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-4">

        <?php if (!isset($_SESSION['username'])): ?>

          <li class="nav-item">
            <a class="nav-link text-white" href="index.php">Home</a>
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
              <a class="nav-link text-white" href="index.php?page=addtasklist">Add Task List</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="logout.php">Logout</a>
            </li>

          <?php elseif ($_SESSION['role'] === 'Admin'): ?>

            <li class="nav-item">
              <a class="nav-link text-white" href="index.php?page=alltasks">All Tasks</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="index.php?page=alltasklists">All Task Lists</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="index.php?page=manageusers">Manage Users</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="index.php?page=addtask">Add Task</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="index.php?page=addtasklist">Add Task List</a>
            </li>

            <li class="nav-item">
              <a class="nav-link text-white" href="logout.php">Logout</a>
            </li>

          <?php endif; ?>

        <?php endif; ?>

      </ul>

      <!-- A11Y toolbar -->
      <div class="a11y-toolbar ms-lg-auto d-flex gap-2 mt-3 mt-lg-0">
        <button type="button" onclick="toggleMode('a11y-dark')">🌙</button>
        <button type="button" onclick="toggleMode('a11y-large-text')">A+</button>
        <button type="button" onclick="toggleMode('a11y-contrast')">⚡</button>
      </div>

    </div>
  </div>
</nav>