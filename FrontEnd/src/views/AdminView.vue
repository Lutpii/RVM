<template>
  <div class="admin-page">
    <!-- Mobile overlay -->
    <div v-if="mobileSidebarOpen" class="mobile-overlay" @click="mobileSidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="['sidebar', { collapsed: sidebarCollapsed, 'mobile-open': mobileSidebarOpen }]">
      <div class="sidebar-header">
        <span class="sidebar-logo">♻️</span>
        <span class="sidebar-title" v-if="!sidebarCollapsed">RVM Admin</span>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          {{ sidebarCollapsed ? '→' : '←' }}
        </button>
      </div>
      <nav class="sidebar-nav">
        <button v-for="item in navItems" :key="item.id"
          :class="['nav-item', { active: activeTab === item.id }]"
          @click="switchTab(item.id)">
          <span class="nav-icon">{{ item.icon }}</span>
          <span class="nav-label" v-if="!sidebarCollapsed || mobileSidebarOpen">{{ item.label }}</span>
        </button>
      </nav>
      <div class="sidebar-footer" v-if="!sidebarCollapsed || mobileSidebarOpen">
        <div class="admin-info">
          <div class="admin-avatar">{{ auth.user?.name?.charAt(0) || 'A' }}</div>
          <div>
            <div class="admin-name">{{ auth.user?.name }}</div>
            <div class="admin-role">Administrator</div>
          </div>
        </div>
        <div class="sidebar-actions">
          <button class="ctrl-btn" @click="toggleTheme()">{{ theme === 'dark' ? '☀️' : '🌙' }}</button>
          <RouterLink to="/dashboard" class="ctrl-btn">👤 User View</RouterLink>
          <button class="logout-btn-sm" @click="handleLogout">Logout</button>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <main class="admin-main">
      <div class="admin-topbar">
        <div style="display:flex;align-items:center;gap:10px">
          <button class="mobile-menu-btn" @click="mobileSidebarOpen = !mobileSidebarOpen; sidebarCollapsed = false">☰</button>
          <h2 class="page-title">{{ currentNavItem?.label }}</h2>
        </div>
        <div class="topbar-right">
          <span class="live-dot" v-if="isLive"></span>
          <span class="last-updated">Updated: {{ lastUpdated }}</span>
          <button class="refresh-btn" @click="fetchTabData(activeTab, true)">🔄 Refresh</button>
        </div>
      </div>

      <!-- Only this area scrolls — topbar above stays put -->
      <div class="admin-scroll-area">

      <!-- Global error banner -->
      <div v-if="tabError" class="error-banner">
        ⚠️ {{ tabError }}
        <button class="error-close" @click="tabError = ''">✕</button>
      </div>

      <!-- ── DASHBOARD ── -->
      <div v-if="activeTab === 'dashboard'" class="tab-content">
        <div v-if="loadingStats" class="loading-overlay"><div class="spinner-lg"></div></div>
        <div v-else>

          <!-- Live clock -->
          <div class="dash-clock-row">
            <span class="overview-clock">{{ liveTime }}</span>
          </div>

          <!-- Waste overview cards -->
          <div class="overview-cards">
            <div class="overview-card" v-for="mat in overviewMaterials" :key="mat.key">
              <div class="ov-icon">{{ mat.icon }}</div>
              <div class="ov-label">{{ mat.label }}</div>
              <div class="ov-count">{{ (overviewData[mat.key]?.today ?? 0).toLocaleString() }}</div>
              <div class="ov-sub">collected today</div>
              <div :class="['ov-pct', overviewData[mat.key]?.pct >= 0 ? 'pct-up' : 'pct-down']">
                {{ overviewData[mat.key]?.pct >= 0 ? '↑' : '↓' }}
                {{ Math.abs(overviewData[mat.key]?.pct ?? 0) }}% vs yesterday
              </div>
            </div>
          </div>

          <!-- Stats grid -->
          <div class="stats-grid">
            <div class="stat-card" v-for="stat in statsCards" :key="stat.label">
              <div class="stat-icon">{{ stat.icon }}</div>
              <div class="stat-info">
                <div class="stat-value" :style="stat.color ? { color: stat.color } : {}">{{ stat.value }}</div>
                <div class="stat-label">{{ stat.label }}</div>
              </div>
            </div>
            <!-- Machine Status card -->
            <div class="stat-card machine-status-card">
              <div>
                <div class="ms-icon">🤖</div>
                <div class="ms-label">MACHINE STATUS</div>
                <div :class="['ms-status', activeMachines > 0 ? 'ms-online' : 'ms-offline']">
                  {{ activeMachines > 0 ? 'ONLINE' : 'OFFLINE' }}
                </div>
                <div class="ms-sub">{{ activeMachines }} Jetson Nano active</div>
              </div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="charts-row">
            <div class="section-card chart-card-main">
              <h3 class="card-title-bar"><span class="title-sq"></span> DAILY COLLECTION TREND (LAST 7 DAYS)</h3>
              <div class="chart-wrap">
                <Bar v-if="barChartData" :data="barChartData" :options="barChartOptions" />
                <div v-else class="chart-empty">No data yet</div>
              </div>
            </div>
            <div class="section-card chart-card-side">
              <h3 class="card-title-bar"><span class="title-sq title-sq-yellow"></span> CATEGORY BREAKDOWN</h3>
              <div class="chart-wrap donut-wrap">
                <Doughnut v-if="donutChartData" :data="donutChartData" :options="donutChartOptions" />
                <div v-else class="chart-empty">No data yet</div>
              </div>
            </div>
          </div>

          <!-- Bin alerts -->
          <div class="section-card" v-if="fullBins.length">
            <h3 class="card-title-bar"><span class="title-sq title-sq-red"></span> BIN ALERTS (≥ 90%)</h3>
            <div class="alert-list">
              <div v-for="machine in fullBins" :key="machine.id" class="alert-item">
                <span class="alert-icon">🚨</span>
                <strong>{{ machine.name }}</strong>
                <div class="alert-bins">
                  <span v-if="machine.aluminum_level >= 90" class="bin-tag">Aluminum {{ machine.aluminum_level }}%</span>
                  <span v-if="machine.plastic_level >= 90" class="bin-tag">Plastic {{ machine.plastic_level }}%</span>
                  <span v-if="machine.glass_level >= 90" class="bin-tag">Glass {{ machine.glass_level }}%</span>
                  <span v-if="machine.paper_level >= 90" class="bin-tag">Paper {{ machine.paper_level }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Material stats + System Status -->
          <div class="breakdown-status-row">
            <div class="section-card" style="margin-bottom:0">
              <div class="card-header">
                <h3 class="card-title-bar"><span class="title-sq"></span> MATERIAL BREAKDOWN</h3>
                <span class="table-period-badge">Last 7 Days</span>
              </div>
              <div class="table-wrap">
                <table class="data-table">
                  <thead>
                    <tr>
                      <th>Material</th><th>Items</th><th>Total Weight</th><th>Points</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="mat in materialStats" :key="mat.material_selected">
                      <td>{{ getMaterialIcon(mat.material_selected) }} {{ capitalize(mat.material_selected) }}</td>
                      <td>{{ mat.count }}</td>
                      <td>{{ (mat.total_weight / 1000).toFixed(2) }} kg</td>
                      <td class="pts-green">{{ mat.total_points }}</td>
                    </tr>
                    <tr v-if="!materialStats.length">
                      <td colspan="4" class="empty-cell">No data for last 7 days</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- System Status -->
            <div class="section-card" style="margin-bottom:0">
              <h3 class="card-title-bar"><span class="title-sq"></span> ⚙️ SYSTEM STATUS</h3>
              <div class="sys-grid">
                <div v-for="item in systemStatusItems" :key="item.label" class="sys-item">
                  <span :class="['sys-dot', 'sys-dot-' + item.color]"></span>
                  <span class="sys-label">{{ item.label }}</span>
                  <span :class="['sys-value', 'sys-val-' + item.color]">{{ item.value }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Admin Controls -->
          <div class="section-card">
            <h3 class="card-title-bar"><span class="title-sq"></span> ADMIN CONTROLS</h3>
            <div class="controls-grid">
              <button class="ctrl-card" @click="exportCsv">
                <span class="ctrl-card-icon ctrl-blue">📤</span>
                <span class="ctrl-card-label">Export Report (CSV)</span>
              </button>
              <button class="ctrl-card" @click="resetAllAlerts">
                <span class="ctrl-card-icon ctrl-yellow">🔕</span>
                <span class="ctrl-card-label">Dismiss Alerts</span>
              </button>
              <button class="ctrl-card" @click="switchTab('machines')">
                <span class="ctrl-card-icon ctrl-purple">🏭</span>
                <span class="ctrl-card-label">View Machine Report</span>
              </button>
              <button class="ctrl-card" @click="requestBinCollection">
                <span class="ctrl-card-icon ctrl-gray">🗑️</span>
                <span class="ctrl-card-label">Request Bin Collection</span>
              </button>
            </div>
          </div>

        </div>
      </div>

      <!-- ── TRANSACTIONS ── -->
      <div v-if="activeTab === 'transactions'" class="tab-content">
        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> TRANSACTION LOG</h3>
            <div class="topbar-right">
              <input v-model="txSearch" @keyup.enter="searchTransactions" placeholder="Search user / item... (press Enter)" class="search-input" />
              <select v-model="txFilter" @change="filterTransactions" class="filter-select">
                <option value="">All</option>
                <option value="valid">OK</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
          </div>
          <div v-if="loadingTransactions" class="loading-overlay"><div class="spinner-lg"></div></div>
          <div v-else class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>TIME</th>
                  <th>USER ID</th>
                  <th>ITEM</th>
                  <th>AI CONFIDENCE</th>
                  <th>POINTS</th>
                  <th>COMPARTMENT</th>
                  <th>STATUS</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tx in transactions" :key="tx.id">
                  <td class="mono small">{{ timeOnly(tx.created_at) }}</td>
                  <td class="mono">{{ formatUserId(tx.user) }}</td>
                  <td>
                    <span class="item-cell">
                      {{ getMaterialIcon(tx.material_selected) }}
                      {{ capitalize(tx.material_selected) }}
                    </span>
                  </td>
                  <td>
                    <div class="confidence-wrap">
                      <div class="confidence-bar">
                        <div class="confidence-fill" :style="{ width: Math.round((tx.ai_confidence || 0) * 100) + '%' }"></div>
                      </div>
                      <span class="confidence-pct">{{ Math.round((tx.ai_confidence || 0) * 100) }}%</span>
                    </div>
                  </td>
                  <td :class="tx.points_earned > 0 ? 'pts-green' : 'muted'">
                    {{ tx.points_earned > 0 ? '+' + tx.points_earned : '0' }}
                  </td>
                  <td class="compartment">{{ getCompartment(tx) }}</td>
                  <td>
                    <span :class="tx.is_valid ? 'badge-ok' : 'badge-rejected'">
                      {{ tx.is_valid ? 'OK' : 'REJECTED' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="!transactions.length">
                  <td colspan="7" class="empty-cell">No transactions found</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pagination-bar">
            <span class="pagination-label">{{ paginationLabel({ currentPage: txPage, perPage: txPerPage, total: txTotal }) }}</span>
            <div class="pagination-controls">
              <select v-model.number="txPerPage" @change="changeTxPerPage" class="filter-select">
                <option :value="15">15 / page</option>
                <option :value="25">25 / page</option>
                <option :value="50">50 / page</option>
                <option :value="100">100 / page</option>
                <option :value="200">200 / page</option>
              </select>
              <button class="action-btn" :disabled="txPage <= 1" @click="goToTxPage(txPage - 1)">← Prev</button>
              <span class="pagination-page">Page {{ txPage }} of {{ txLastPage }}</span>
              <button class="action-btn" :disabled="txPage >= txLastPage" @click="goToTxPage(txPage + 1)">Next →</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── USERS ── -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div v-if="loadingUsers" class="loading-overlay"><div class="spinner-lg"></div></div>
        <div v-else class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> ALL USERS</h3>
            <input v-model="userSearch" @keyup.enter="searchUsers" placeholder="Search users... (press Enter)" class="search-input" />
          </div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th><th>Name</th><th>Email / Phone</th><th>Points</th><th>Role</th><th>Verified</th><th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td class="muted mono">{{ formatUserId(user) }}</td>
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-sm">{{ user.name?.charAt(0) }}</div>
                      {{ user.name }}
                    </div>
                  </td>
                  <td class="muted">{{ user.email || user.phone }}</td>
                  <td class="pts-green">{{ user.total_points?.toLocaleString() }}</td>
                  <td><span :class="['role-badge', 'role-' + user.role]">{{ user.role }}</span></td>
                  <td>{{ user.is_verified ? '✅' : '❌' }}</td>
                  <td>
                    <button class="action-btn edit-btn" @click="editUser(user)">Edit</button>
                    <button class="action-btn del-btn" @click="deleteUser(user.id)">Delete</button>
                  </td>
                </tr>
                <tr v-if="!users.length">
                  <td colspan="7" class="empty-cell">No users found</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pagination-bar">
            <span class="pagination-label">{{ paginationLabel({ currentPage: usersPage, perPage: usersPerPage, total: usersTotal }) }}</span>
            <div class="pagination-controls">
              <select v-model.number="usersPerPage" @change="changeUsersPerPage" class="filter-select">
                <option :value="15">15 / page</option>
                <option :value="25">25 / page</option>
                <option :value="50">50 / page</option>
                <option :value="100">100 / page</option>
                <option :value="200">200 / page</option>
              </select>
              <button class="action-btn" :disabled="usersPage <= 1" @click="goToUsersPage(usersPage - 1)">← Prev</button>
              <span class="pagination-page">Page {{ usersPage }} of {{ usersLastPage }}</span>
              <button class="action-btn" :disabled="usersPage >= usersLastPage" @click="goToUsersPage(usersPage + 1)">Next →</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── MACHINES ── -->
      <div v-if="activeTab === 'machines'" class="tab-content">
        <!-- Reward Points Configuration -->
        <div class="section-card">
          <h3 class="card-title-bar"><span class="title-sq title-sq-yellow"></span> REWARD POINTS CONFIGURATION</h3>
          <div class="reward-materials">
            <div class="reward-mat-card" v-for="mat in rewardMaterials" :key="mat.key">
              <div class="reward-mat-label">{{ mat.icon }} {{ mat.label }}</div>
              <div class="reward-mat-control">
                <input class="reward-input" type="number" v-model.number="rewardEditValues[mat.key]" min="0" max="9999" />
                <button class="update-btn" @click="updateReward(mat.key)" :disabled="savingReward === mat.key">
                  {{ savingReward === mat.key ? '...' : 'Update' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> RVM MACHINES</h3>
            <button class="add-btn" @click="openAddMachine">+ Add Machine</button>
          </div>
          <div v-if="loadingMachines" class="loading-overlay"><div class="spinner-lg"></div></div>
          <div v-else class="machines-grid">
            <div v-for="machine in adminMachines" :key="machine.id" class="machine-admin-card">
              <div class="machine-admin-header">
                <div>
                  <strong>{{ machine.name }}</strong>
                  <span class="machine-code-badge">{{ machine.machine_code }}</span>
                </div>
                <span :class="['status-badge', 'status-' + machine.status]">{{ machine.status }}</span>
              </div>
              <p class="machine-loc">📍 {{ machine.location_name || 'No location set' }}</p>
              <div class="bin-admin-grid">
                <div v-for="bin in binTypes" :key="bin.id" class="bin-admin">
                  <span>{{ bin.icon }} {{ bin.label }}</span>
                  <div class="bin-bar-sm">
                    <div :class="['bin-fill-sm', getBinClass(machine[bin.id + '_level'])]"
                      :style="{ width: machine[bin.id + '_level'] + '%' }"></div>
                  </div>
                  <span :class="machine[bin.id + '_level'] >= 90 ? 'text-red' : 'text-muted-sm'">
                    {{ machine[bin.id + '_level'] }}%
                  </span>
                </div>
              </div>
              <div class="machine-actions">
                <button class="action-btn edit-btn" @click="openEditMachine(machine)">Edit</button>
                <button class="action-btn" @click="resetBins(machine)">Reset Bins</button>
                <button class="action-btn del-btn" @click="deleteMachine(machine.id)">Delete</button>
              </div>
            </div>
            <div v-if="!adminMachines.length" class="empty-machines">
              <p>No machines registered. Add your first RVM machine.</p>
              <button class="add-btn" @click="openAddMachine">+ Add Machine</button>
            </div>
          </div>
        </div>
      </div>

      <!-- ── SESSIONS ── -->
      <div v-if="activeTab === 'sessions'" class="tab-content">
        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> ALL SESSIONS</h3>
            <input v-model="sessionsSearch" @keyup.enter="searchSessions" placeholder="Search user / machine / status... (press Enter)" class="search-input" />
          </div>
          <div v-if="loadingSessions" class="loading-overlay"><div class="spinner-lg"></div></div>
          <div v-else class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th><th>User</th><th>Machine</th><th>Status</th><th>Items</th><th>Points</th><th>Started</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in sessions" :key="s.id">
                  <td class="mono small">{{ s.session_code }}</td>
                  <td>{{ s.user?.name || 'Guest' }}</td>
                  <td>{{ s.machine?.name || '—' }}</td>
                  <td><span :class="['status-badge', 'status-' + s.status]">{{ s.status }}</span></td>
                  <td>{{ s.total_items }}</td>
                  <td class="pts-green">+{{ s.points_earned }}</td>
                  <td class="muted small">{{ formatDate(s.started_at) }}</td>
                </tr>
                <tr v-if="!sessions.length">
                  <td colspan="7" class="empty-cell">No sessions found</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="pagination-bar">
            <span class="pagination-label">{{ paginationLabel({ currentPage: sessionsPage, perPage: sessionsPerPage, total: sessionsTotal }) }}</span>
            <div class="pagination-controls">
              <select v-model.number="sessionsPerPage" @change="changeSessionsPerPage" class="filter-select">
                <option :value="15">15 / page</option>
                <option :value="25">25 / page</option>
                <option :value="50">50 / page</option>
                <option :value="100">100 / page</option>
                <option :value="200">200 / page</option>
              </select>
              <button class="action-btn" :disabled="sessionsPage <= 1" @click="goToSessionsPage(sessionsPage - 1)">← Prev</button>
              <span class="pagination-page">Page {{ sessionsPage }} of {{ sessionsLastPage }}</span>
              <button class="action-btn" :disabled="sessionsPage >= sessionsLastPage" @click="goToSessionsPage(sessionsPage + 1)">Next →</button>
            </div>
          </div>
        </div>
      </div>

      </div>
    </main>

    <!-- ── Edit User Modal ── -->
    <div v-if="editingUser" class="modal-overlay" @click.self="editingUser = null">
      <div class="modal">
        <h3>Edit User</h3>
        <div class="form-group">
          <label>Name</label>
          <input v-model="editingUser.name" type="text" />
        </div>
        <div class="form-group">
          <label>Total Points</label>
          <input v-model.number="editingUser.total_points" type="number" />
        </div>
        <div class="form-group">
          <label>Role</label>
          <select v-model="editingUser.role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <div class="form-group">
          <label>Verified</label>
          <select v-model="editingUser.is_verified">
            <option :value="1">Yes</option>
            <option :value="0">No</option>
          </select>
        </div>
        <div class="modal-actions">
          <button class="action-btn" @click="editingUser = null">Cancel</button>
          <button class="action-btn edit-btn" @click="saveUser">Save</button>
        </div>
      </div>
    </div>

    <!-- ── Add Machine Modal ── -->
    <div v-if="showAddMachine" class="modal-overlay" @click.self="showAddMachine = false">
      <div class="modal">
        <h3>Add New Machine</h3>
        <div class="form-group">
          <label>Machine Code *</label>
          <input v-model="newMachine.machine_code" type="text" placeholder="e.g. RVM-001" />
        </div>
        <div class="form-group">
          <label>Machine Name *</label>
          <input v-model="newMachine.name" type="text" placeholder="e.g. RVM Lobby A" />
        </div>
        <div class="form-group">
          <label>Location</label>
          <input v-model="newMachine.location_name" type="text" placeholder="e.g. Block A, Ground Floor" />
        </div>
        <div class="form-group">
          <label>Status</label>
          <select v-model="newMachine.status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="maintenance">Maintenance</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Latitude</label>
            <input v-model="newMachine.latitude" type="number" step="any" placeholder="3.1234" />
          </div>
          <div class="form-group">
            <label>Longitude</label>
            <input v-model="newMachine.longitude" type="number" step="any" placeholder="103.1234" />
          </div>
        </div>
        <p v-if="machineError" class="form-error">{{ machineError }}</p>
        <div class="modal-actions">
          <button class="action-btn" @click="showAddMachine = false">Cancel</button>
          <button class="action-btn edit-btn" @click="addMachine" :disabled="savingMachine">
            {{ savingMachine ? 'Adding...' : 'Add Machine' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Edit Machine Modal ── -->
    <div v-if="editingMachine" class="modal-overlay" @click.self="editingMachine = null">
      <div class="modal">
        <h3>Edit Machine</h3>
        <div class="form-group">
          <label>Machine Code</label>
          <input v-model="editingMachine.machine_code" type="text" disabled class="input-disabled" />
        </div>
        <div class="form-group">
          <label>Machine Name *</label>
          <input v-model="editingMachine.name" type="text" />
        </div>
        <div class="form-group">
          <label>Location</label>
          <input v-model="editingMachine.location_name" type="text" />
        </div>
        <div class="form-group">
          <label>Status</label>
          <select v-model="editingMachine.status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="maintenance">Maintenance</option>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Latitude</label>
            <input v-model="editingMachine.latitude" type="number" step="any" />
          </div>
          <div class="form-group">
            <label>Longitude</label>
            <input v-model="editingMachine.longitude" type="number" step="any" />
          </div>
        </div>
        <p v-if="machineError" class="form-error">{{ machineError }}</p>
        <div class="modal-actions">
          <button class="action-btn" @click="editingMachine = null">Cancel</button>
          <button class="action-btn edit-btn" @click="saveMachine" :disabled="savingMachine">
            {{ savingMachine ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Confirm modal (replaces native confirm()) ── -->
    <div v-if="confirmState.show" class="modal-overlay" @click.self="settleConfirm(false)">
      <div class="modal confirm-modal">
        <p class="confirm-message">{{ confirmState.message }}</p>
        <div class="modal-actions">
          <button class="action-btn" @click="settleConfirm(false)">Cancel</button>
          <button
            :class="['action-btn', confirmState.variant === 'danger' ? 'del-btn' : 'edit-btn']"
            @click="settleConfirm(true)"
          >OK</button>
        </div>
      </div>
    </div>

    <!-- ── Toast (replaces native alert()) ── -->
    <Transition name="toast-fade">
      <div v-if="toastState.show" :class="['toast', toastState.type]" role="status">
        <span class="toast-icon">{{ toastState.type === 'error' ? '⚠️' : '✅' }}</span>
        {{ toastState.message }}
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, computed, inject, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'
import { resolveLoadingFlag } from '@/utils/admin/tabLoading.js'
import { paginationLabel } from '@/utils/admin/paginationLabel.js'
import { buildRewardUpdatePayload } from '@/utils/admin/rewardConfig.js'
import { validateMachineName } from '@/utils/admin/validateMachine.js'
import { normalizeMachine } from '@/utils/admin/normalizeMachine.js'
import { isFresh } from '@/utils/admin/tabFreshness.js'
import {
  Chart as ChartJS, CategoryScale, LinearScale, BarElement,
  ArcElement, Tooltip, Legend
} from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'
ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend)

const router = useRouter()
const auth = useAuthStore()
const theme = inject('theme')
const toggleTheme = inject('toggleTheme')

// ── State ──
const activeTab = ref('dashboard')
const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)
const lastUpdated = ref('—')
const isLive = ref(true)

const loadingStats = ref(false)
const loadingUsers = ref(false)
const loadingMachines = ref(false)
const loadingSessions = ref(false)
const loadingTransactions = ref(false)

const tabError = ref('')   // surfaced error message per tab

// Skip re-fetching a tab if it was loaded this recently — makes rapid tab
// switching feel instant instead of flashing a spinner every click.
const TAB_STALE_MS = 10000
const tabFetchedAt = reactive({ dashboard: 0, transactions: 0, users: 0, machines: 0, sessions: 0 })

const userSearch = ref('')
const txSearch = ref('')
const txFilter = ref('')
const sessionsSearch = ref('')

const usersPage = ref(1)
const usersPerPage = ref(15)
const usersTotal = ref(0)
const usersLastPage = ref(1)

const txPage = ref(1)
const txPerPage = ref(15)
const txTotal = ref(0)
const txLastPage = ref(1)

const sessionsPage = ref(1)
const sessionsPerPage = ref(15)
const sessionsTotal = ref(0)
const sessionsLastPage = ref(1)

const editingUser = ref(null)
const editingMachine = ref(null)
const showAddMachine = ref(false)
const savingMachine = ref(false)

// ── Confirm modal + toast (replaces native confirm()/alert()) ──
const confirmState = ref({ show: false, message: '', variant: 'danger', resolve: null })
function askConfirm(message, variant = 'danger') {
  return new Promise((resolve) => {
    confirmState.value = { show: true, message, variant, resolve }
  })
}
function settleConfirm(result) {
  confirmState.value.resolve?.(result)
  confirmState.value.show = false
}

const toastState = ref({ show: false, message: '', type: 'success' })
let toastTimer = null
function showToast(message, type = 'success') {
  toastState.value = { show: true, message, type }
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toastState.value.show = false }, 3500)
}
const machineError = ref('')
const savingReward = ref('')

const statsData = ref({})
const users = ref([])
const adminMachines = ref([])
const sessions = ref([])
const transactions = ref([])
const materialStats = ref([])
const recentSessions = ref([])
const fullBins = ref([])

const rewardEditValues = ref({ plastic: 5, aluminum: 8, glass: 5, paper: 3 })
const savedRewardValues = ref({ plastic: 5, aluminum: 8, glass: 5, paper: 3 })

const barChartData = ref(null)
const donutChartData = ref(null)
const overviewData = ref({})
const liveTime = ref('')
let clockTimer = null

const CHART_COLORS = {
  plastic:  '#5B8FF9',
  aluminum: '#F6AD3C',
  paper:    '#4BC5A3',
  glass:    '#A78BFA',
}

const barChartOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { labels: { color: '#9ca3af' } } },
  scales: {
    x: { ticks: { color: '#9ca3af' }, grid: { color: 'rgba(255,255,255,0.05)' } },
    y: {
      ticks: { color: '#9ca3af' },
      grid: { color: 'rgba(255,255,255,0.05)' },
      title: { display: true, text: 'Weight (g)', color: '#6b7280', font: { size: 11 } },
    },
  },
}

const donutChartOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom', labels: { color: '#9ca3af', padding: 16 } } },
  cutout: '65%',
}

const newMachine = ref({
  machine_code: '', name: '', location_name: '', status: 'active', latitude: '', longitude: '',
})

let refreshTimer = null

// ── Config ──
const navItems = [
  { id: 'dashboard',    icon: '📊', label: 'Dashboard' },
  { id: 'transactions', icon: '📑', label: 'Transactions' },
  { id: 'users',        icon: '👥', label: 'Users' },
  { id: 'machines',     icon: '🏭', label: 'Machines' },
  { id: 'sessions',     icon: '📋', label: 'Sessions' },
]

const binTypes = [
  { id: 'aluminum', icon: '🥫', label: 'Aluminum' },
  { id: 'plastic',  icon: '🧴', label: 'Plastic'  },
  { id: 'glass',    icon: '🍶', label: 'Glass'    },
  { id: 'paper',    icon: '📄', label: 'Paper'    },
]

const rewardMaterials = [
  { key: 'plastic',  icon: '🧴', label: 'Plastic Bottle (pts)' },
  { key: 'aluminum', icon: '🥫', label: 'Aluminium Can (pts)'  },
  { key: 'paper',    icon: '📄', label: 'Paper / Card (pts)'   },
  { key: 'glass',    icon: '🍶', label: 'Glass Bottle (pts)'   },
]

const overviewMaterials = [
  { key: 'plastic',  icon: '🧴', label: 'PLASTIC BOTTLES'   },
  { key: 'aluminum', icon: '🥫', label: 'ALUMINIUM CANS'    },
  { key: 'paper',    icon: '📄', label: 'PAPER / RECYCLABLES'},
  { key: 'glass',    icon: '🍶', label: 'GLASS BOTTLES'     },
]

const COMPARTMENTS = { plastic: 'A', aluminum: 'B', paper: 'C', glass: 'D' }

// ── Computed ──
const currentNavItem = computed(() => navItems.find(n => n.id === activeTab.value))

const statsCards = computed(() => [
  { icon: '👥', label: 'Total Users',    value: statsData.value.total_users    || 0 },
  { icon: '🏭', label: 'Machines',       value: statsData.value.total_machines || 0 },
  { icon: '🔄', label: 'Active Sessions',value: statsData.value.active_sessions || 0 },
  { icon: '⚖️', label: 'Weight (kg)',    value: statsData.value.total_weight_kg || 0 },
  { icon: '⭐', label: 'Points Issued',  value: (statsData.value.total_points_given || 0).toLocaleString(), color: 'var(--accent-green)' },
  { icon: '📦', label: 'Transactions',  value: statsData.value.total_transactions || 0 },
])

const activeMachines = computed(() =>
  adminMachines.value.filter(m => m.status === 'active').length
)

const systemStatusItems = computed(() => {
  const machines = adminMachines.value
  const activeSessions = statsData.value.active_sessions ?? 0

  const binLevel = (key) => machines.length
    ? Math.max(...machines.map(m => m[key] ?? 0)) : 0
  const binColor = (pct) => pct >= 90 ? 'red' : pct >= 70 ? 'yellow' : 'green'
  const binLabel = (pct, color) => `${pct}% ${color === 'green' ? 'OK' : 'WARN'}`

  const plastic  = binLevel('plastic_level')
  const aluminum = binLevel('aluminum_level')
  const paper    = binLevel('paper_level')
  const glass    = binLevel('glass_level')

  const totalMachines  = machines.length
  const onlineMachines = machines.filter(m => m.status === 'active').length
  const machineColor   = onlineMachines > 0 ? 'green' : 'red'

  const cameraColor = activeSessions > 0 ? 'green' : 'yellow'
  const cameraValue = activeSessions > 0 ? `LIVE (${activeSessions})` : 'STANDBY'

  const rewardOk    = Object.values(savedRewardValues.value).every(v => v > 0)
  const rewardColor = rewardOk ? 'green' : 'yellow'

  return [
    { label: 'Camera',       value: cameraValue,                           color: cameraColor  },
    { label: 'Jetson Nano',  value: `${onlineMachines}/${totalMachines} ONLINE`, color: machineColor },
    { label: 'Reward Engine',value: rewardOk ? 'OK' : 'CHECK CONFIG',      color: rewardColor  },
    { label: 'Plastic Bin',  value: binLabel(plastic,  binColor(plastic)),  color: binColor(plastic)  },
    { label: 'Aluminum Bin', value: binLabel(aluminum, binColor(aluminum)), color: binColor(aluminum) },
    { label: 'Paper Bin',    value: binLabel(paper,    binColor(paper)),    color: binColor(paper)    },
    { label: 'Glass Bin',    value: binLabel(glass,    binColor(glass)),    color: binColor(glass)    },
  ]
})

// ── Helpers ──
function getMaterialIcon(mat) {
  return { aluminum: '🥫', plastic: '🧴', glass: '🍶', paper: '📄' }[mat] || '♻️'
}

function getBinClass(level) {
  if (level >= 90) return 'bin-danger'
  if (level >= 70) return 'bin-warning'
  return 'bin-ok'
}

function fmtTime(d, withSeconds = false) {
  let h = d.getHours(), m = d.getMinutes(), s = d.getSeconds()
  const ampm = h >= 12 ? 'pm' : 'am'
  h = h % 12 || 12
  const hh = String(h).padStart(2, '0')
  const mm = String(m).padStart(2, '0')
  const ss = String(s).padStart(2, '0')
  return withSeconds ? `${hh}:${mm}:${ss} ${ampm}` : `${hh}:${mm} ${ampm}`
}

function formatDate(ts) {
  if (!ts) return '—'
  const d = new Date(ts)
  return d.toLocaleDateString() + ' ' + fmtTime(d)
}

function timeOnly(ts) {
  if (!ts) return '—'
  return fmtTime(new Date(ts), true)
}

function capitalize(str) {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
}

function formatUserId(user) {
  if (!user) return 'GUEST'
  if (user.role === 'admin') return 'ADMIN-' + String(user.id).padStart(3, '0')
  return 'USR-' + String(user.id).padStart(4, '0')
}

function getCompartment(tx) {
  if (!tx.is_valid) return '—'
  return COMPARTMENTS[tx.material_selected] || '—'
}

// ── Data fetching ──
async function fetchTabData(tab, showSpinner = false) {
  lastUpdated.value = fmtTime(new Date(), true)
  tabError.value = ''
  try {
    if (tab === 'dashboard') {
      if (showSpinner) loadingStats.value = true
      const res = await api.get('/admin/stats')
      statsData.value      = res.data.stats                   || {}
      materialStats.value  = res.data.stats?.material_stats   || []
      recentSessions.value = res.data.stats?.recent_sessions  || []
      fullBins.value       = res.data.stats?.full_bins        || []
    } else if (tab === 'transactions') {
      if (showSpinner) loadingTransactions.value = true
      const res = await api.get('/admin/transactions', { params: {
        page: txPage.value, per_page: txPerPage.value,
        search: txSearch.value || undefined, status: txFilter.value || undefined,
      } })
      transactions.value = res.data.transactions?.data || []
      txTotal.value     = res.data.transactions?.total ?? 0
      txLastPage.value  = res.data.transactions?.last_page ?? 1
    } else if (tab === 'users') {
      if (showSpinner) loadingUsers.value = true
      const res = await api.get('/admin/users', { params: {
        page: usersPage.value, per_page: usersPerPage.value, search: userSearch.value || undefined,
      } })
      users.value = res.data.users?.data || []
      usersTotal.value    = res.data.users?.total ?? 0
      usersLastPage.value = res.data.users?.last_page ?? 1
    } else if (tab === 'machines') {
      if (showSpinner) loadingMachines.value = true
      const res = await api.get('/admin/machines')
      adminMachines.value = (res.data.machines || []).map(normalizeMachine)
    } else if (tab === 'sessions') {
      if (showSpinner) loadingSessions.value = true
      const res = await api.get('/admin/sessions', { params: {
        page: sessionsPage.value, per_page: sessionsPerPage.value, search: sessionsSearch.value || undefined,
      } })
      sessions.value = res.data.sessions?.data || []
      sessionsTotal.value    = res.data.sessions?.total ?? 0
      sessionsLastPage.value = res.data.sessions?.last_page ?? 1
    }
    tabFetchedAt[tab] = Date.now()
  } catch (err) {
    const msg = err.response?.data?.message || err.message || 'Failed to load data'
    tabError.value = msg
    console.error(`fetchTabData(${tab}):`, err.response?.data || err.message)
  } finally {
    const flags = { loadingStats, loadingUsers, loadingMachines, loadingSessions, loadingTransactions }
    const flagName = resolveLoadingFlag(tab)
    if (flagName) flags[flagName].value = false
  }
}

async function fetchRewardConfig() {
  try {
    const res = await api.get('/admin/reward-config')
    rewardEditValues.value = { ...rewardEditValues.value, ...res.data.config }
    savedRewardValues.value = { ...savedRewardValues.value, ...res.data.config }
  } catch {}
}

async function fetchChartData() {
  try {
    const res = await api.get('/admin/chart-data')
    const { labels, datasets, breakdown, overview } = res.data
    overviewData.value = overview || {}

    barChartData.value = {
      labels,
      datasets: [
        { label: 'Plastic',  data: datasets.plastic,  backgroundColor: CHART_COLORS.plastic  },
        { label: 'Aluminum', data: datasets.aluminum, backgroundColor: CHART_COLORS.aluminum },
        { label: 'Paper',    data: datasets.paper,    backgroundColor: CHART_COLORS.paper    },
        { label: 'Glass',    data: datasets.glass,    backgroundColor: CHART_COLORS.glass    },
      ],
    }

    donutChartData.value = {
      labels: ['Plastic', 'Aluminum', 'Paper', 'Glass'],
      datasets: [{
        data: [breakdown.plastic, breakdown.aluminum, breakdown.paper, breakdown.glass],
        backgroundColor: [CHART_COLORS.plastic, CHART_COLORS.aluminum, CHART_COLORS.paper, CHART_COLORS.glass],
        borderWidth: 0,
      }],
    }

    // Chart.js measures its container on mount via ResizeObserver — on some
    // mobile browsers that first measurement can be wrong (seen as the chart
    // rendering far wider than the screen, pushing the whole card off-screen)
    // and nothing naturally fires again afterward to correct it. Nudging a
    // resize event once the canvas has actually mounted forces every chart on
    // the page to remeasure against its real, current container size.
    await nextTick()
    window.dispatchEvent(new Event('resize'))
  } catch {}
}

// The dashboard's charts are unmounted/remounted (v-if) every time this tab
// is left and returned to, without necessarily re-fetching data — so the
// resize nudge in fetchChartData() alone doesn't cover that path. Re-fire it
// on every remount too.
watch(activeTab, async (tab) => {
  if (tab !== 'dashboard') return
  await nextTick()
  window.dispatchEvent(new Event('resize'))
})

function switchTab(tab) {
  activeTab.value = tab
  mobileSidebarOpen.value = false
  if (isFresh(tabFetchedAt[tab], Date.now(), TAB_STALE_MS)) return
  fetchTabData(tab, true)
}

// ── Pagination / search (Users, Transactions, Sessions) ──
function searchUsers() {
  usersPage.value = 1
  fetchTabData('users', true)
}
function goToUsersPage(page) {
  usersPage.value = page
  fetchTabData('users', true)
}
function changeUsersPerPage() {
  usersPage.value = 1
  fetchTabData('users', true)
}

function searchTransactions() {
  txPage.value = 1
  fetchTabData('transactions', true)
}
function filterTransactions() {
  txPage.value = 1
  fetchTabData('transactions', true)
}
function goToTxPage(page) {
  txPage.value = page
  fetchTabData('transactions', true)
}
function changeTxPerPage() {
  txPage.value = 1
  fetchTabData('transactions', true)
}

function searchSessions() {
  sessionsPage.value = 1
  fetchTabData('sessions', true)
}
function goToSessionsPage(page) {
  sessionsPage.value = page
  fetchTabData('sessions', true)
}
function changeSessionsPerPage() {
  sessionsPage.value = 1
  fetchTabData('sessions', true)
}

// ── Admin Controls ──
async function exportCsv() {
  try {
    const res = await api.get('/admin/export-csv', { responseType: 'blob' })
    const url = URL.createObjectURL(res.data)
    const a = document.createElement('a')
    a.href = url
    a.download = `rvm_report_${new Date().toISOString().slice(0, 10)}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch {
    showToast('Export failed. Please try again.', 'error')
  }
}

// Dismisses the warning list only — deliberately does NOT touch bin levels.
// A bin doesn't become empty just because the dashboard notice was cleared;
// only actually emptying it (Machines tab → Reset Bins) should do that. The
// dismissal is local to this session: since the underlying levels are
// untouched, the next natural refresh (30s timer, tab revisit) will show the
// alert again for any bin that's still genuinely ≥90% — that's intentional,
// not a bug, the same way a "disk full" warning shouldn't stay dismissed
// forever while the disk is still full.
function resetAllAlerts() {
  fullBins.value = []
  showToast('Alerts dismissed. Bin levels are unchanged — empty a bin from the Machines tab to clear it for good.')
}

async function requestBinCollection() {
  if (!(await askConfirm('Send a bin collection request?', 'warning'))) return
  try {
    // Deliberately a separate endpoint from resetAllAlerts() — this only logs
    // the request, it must not zero out bin levels (the bin isn't actually
    // empty yet, someone just needs to go collect it).
    await api.post('/admin/request-bin-collection')
    showToast('Bin collection request sent.')
  } catch {
    showToast('Failed to send collection request.', 'error')
  }
}

// ── Reward Config ──
async function updateReward(material) {
  savingReward.value = material
  try {
    const config = buildRewardUpdatePayload(savedRewardValues.value, rewardEditValues.value, material)
    await api.put('/admin/reward-config', config)
    savedRewardValues.value = config
    rewardEditValues.value = { ...rewardEditValues.value, [material]: config[material] }
    const label = material.charAt(0).toUpperCase() + material.slice(1)
    showToast(`${label} reward updated to ${config[material]} pts/item.`)
  } catch (e) {
    showToast(e.message?.startsWith('Invalid value') ? e.message : 'Failed to update reward config.', 'error')
  } finally {
    savingReward.value = ''
  }
}

// ── User management ──
function editUser(user) { editingUser.value = { ...user } }

async function saveUser() {
  try {
    await api.put(`/admin/users/${editingUser.value.id}`, editingUser.value)
    const idx = users.value.findIndex(u => u.id === editingUser.value.id)
    if (idx > -1) users.value[idx] = { ...editingUser.value }
    const name = editingUser.value.name
    editingUser.value = null
    showToast(`User "${name}" saved.`)
  } catch { showToast('Failed to save user.', 'error') }
}

async function deleteUser(id) {
  if (!(await askConfirm('Delete this user? This cannot be undone.'))) return
  try {
    await api.delete(`/admin/users/${id}`)
    users.value = users.value.filter(u => u.id !== id)
  } catch { showToast('Failed to delete user.', 'error') }
}

// ── Machine management ──
function openAddMachine() {
  newMachine.value = { machine_code: '', name: '', location_name: '', status: 'active', latitude: '', longitude: '' }
  machineError.value = ''
  showAddMachine.value = true
}

function openEditMachine(machine) {
  editingMachine.value = { ...machine }
  machineError.value = ''
}

async function addMachine() {
  if (!newMachine.value.machine_code?.trim() || validateMachineName(newMachine.value.name)) {
    machineError.value = 'Machine code and name are required.'
    return
  }
  savingMachine.value = true
  machineError.value = ''
  try {
    const payload = {
      machine_code:  newMachine.value.machine_code.trim(),
      name:          newMachine.value.name.trim(),
      location_name: newMachine.value.location_name?.trim() || null,
      status:        newMachine.value.status || 'active',
      latitude:      newMachine.value.latitude !== '' ? newMachine.value.latitude : null,
      longitude:     newMachine.value.longitude !== '' ? newMachine.value.longitude : null,
    }
    const res = await api.post('/admin/machines', payload)
    adminMachines.value.push(normalizeMachine(res.data.machine))
    showAddMachine.value = false
    showToast(`Machine "${payload.name}" added.`)
  } catch (e) {
    const errors = e.response?.data?.errors
    if (errors) {
      machineError.value = Object.values(errors).flat().join(' ')
    } else {
      machineError.value = e.response?.data?.message || 'Failed to add machine.'
    }
  } finally {
    savingMachine.value = false
  }
}

async function saveMachine() {
  const nameError = validateMachineName(editingMachine.value.name)
  if (nameError) {
    machineError.value = nameError
    return
  }
  machineError.value = ''
  savingMachine.value = true
  try {
    const payload = {
      name:          editingMachine.value.name.trim(),
      location_name: editingMachine.value.location_name?.trim() || null,
      status:        editingMachine.value.status,
      latitude:      editingMachine.value.latitude !== '' ? editingMachine.value.latitude : null,
      longitude:     editingMachine.value.longitude !== '' ? editingMachine.value.longitude : null,
    }
    const res = await api.put(`/admin/machines/${editingMachine.value.id}`, payload)
    const idx = adminMachines.value.findIndex(m => m.id === editingMachine.value.id)
    if (idx > -1) adminMachines.value[idx] = normalizeMachine(res.data.machine)
    editingMachine.value = null
    showToast(`Machine "${payload.name}" saved.`)
  } catch (e) {
    const errors = e.response?.data?.errors
    machineError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Failed to save machine.')
  } finally {
    savingMachine.value = false
  }
}

async function deleteMachine(id) {
  if (!(await askConfirm('Delete this machine? This cannot be undone.'))) return
  try {
    await api.delete(`/admin/machines/${id}`)
    adminMachines.value = adminMachines.value.filter(m => m.id !== id)
  } catch { showToast('Failed to delete machine.', 'error') }
}

async function resetBins(machine) {
  if (!(await askConfirm(`Reset all bin levels for ${machine.name}?`, 'warning'))) return
  try {
    await api.put(`/admin/machines/${machine.id}/bin-levels`, {
      aluminum_level: 0, plastic_level: 0, glass_level: 0, paper_level: 0,
    })
    machine.aluminum_level = machine.plastic_level = machine.glass_level = machine.paper_level = 0
    showToast(`Bin levels reset for ${machine.name}.`)
  } catch { showToast('Failed to reset bins.', 'error') }
}

async function handleLogout() {
  await auth.logout()
  router.push('/')
}

// ── Lifecycle ──
function updateClock() {
  const now = new Date()
  liveTime.value = now.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
    + ', ' + fmtTime(now, true)
}

onMounted(async () => {
  updateClock()
  clockTimer = setInterval(updateClock, 1000)
  // Load all tabs in parallel so switching is instant and machines are available immediately
  await Promise.allSettled([
    fetchTabData('dashboard', true),
    fetchTabData('machines', true),
    fetchTabData('users', true),
    fetchTabData('sessions', true),
    fetchRewardConfig(),
    fetchChartData(),
  ])
  refreshTimer = setInterval(() => {
    fetchTabData(activeTab.value)
    if (activeTab.value === 'dashboard') fetchChartData()
  }, 30000)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
  if (clockTimer) clearInterval(clockTimer)
})
</script>

<style scoped>
.admin-page {
  display: flex;
  height: 100vh;
  overflow: hidden; /* the shell itself never scrolls — see .admin-scroll-area */
  background: var(--bg-primary);
}

/* ── Sidebar ── */
.sidebar {
  width: 220px;
  min-height: 100vh;
  background: var(--bg-secondary);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  flex-shrink: 0;
}
.sidebar.collapsed { width: 60px; }

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 12px;
  border-bottom: 1px solid var(--border);
}
.sidebar-logo { font-size: 20px; flex-shrink: 0; }
.sidebar-title { font-size: 15px; font-weight: 700; color: var(--text-primary); flex: 1; white-space: nowrap; overflow: hidden; }
.collapse-btn { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 14px; }

.sidebar-nav { flex: 1; padding: 12px 8px; display: flex; flex-direction: column; gap: 4px; justify-content: flex-start; }
.nav-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 8px; border-radius: 8px;
  background: none; border: none; color: var(--text-secondary);
  cursor: pointer; font-size: 14px; transition: all 0.2s; width: 100%; text-align: left;
}
.nav-item:hover { background: var(--bg-card); color: var(--text-primary); }
.nav-item.active { background: rgba(78, 110, 242, 0.15); color: var(--accent-blue); font-weight: 600; }
.nav-icon { font-size: 18px; flex-shrink: 0; }
.nav-label { white-space: nowrap; overflow: hidden; }

.sidebar-footer { padding: 12px; border-top: 1px solid var(--border); }
.admin-info { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.admin-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--grad-header); color: white;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 14px; flex-shrink: 0;
}
.admin-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.admin-role { font-size: 11px; color: var(--text-muted); }
.sidebar-actions { display: flex; flex-direction: column; gap: 6px; }
.ctrl-btn {
  background: var(--bg-card); border: 1px solid var(--border);
  color: var(--text-secondary); padding: 6px 10px;
  border-radius: 6px; cursor: pointer; font-size: 12px;
  text-decoration: none; text-align: center;
}
.logout-btn-sm {
  background: none; border: 1px solid var(--accent-red);
  color: var(--accent-red); padding: 6px 10px;
  border-radius: 6px; cursor: pointer; font-size: 12px;
}

/* ── Main ── */
/* App-shell layout: .admin-main itself never scrolls — it just stacks the
   topbar (fixed size) above .admin-scroll-area (the one scrollable region),
   so the sidebar and topbar stay in place while only content moves. */
.admin-main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
.admin-scroll-area { flex: 1; overflow-y: auto; }

.admin-topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 24px; background: var(--bg-secondary);
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}
.page-title { font-size: 18px; font-weight: 700; color: var(--text-primary); }
.topbar-right { display: flex; align-items: center; gap: 10px; }
.last-updated { font-size: 12px; color: var(--text-muted); }
.refresh-btn {
  background: var(--bg-card); border: 1px solid var(--border);
  color: var(--text-secondary); padding: 6px 12px;
  border-radius: 6px; cursor: pointer; font-size: 13px;
}
.live-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--accent-green);
  animation: pulse 2s ease infinite;
}
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

.tab-content { padding: 20px 24px; }

/* ── Stats grid ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 12px; margin-bottom: 16px;
}
.stat-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 14px;
  display: flex; align-items: center; gap: 12px;
}
.stat-icon { font-size: 24px; }
.stat-value { font-size: 22px; font-weight: 800; color: var(--text-primary); }
.stat-label { font-size: 12px; color: var(--text-muted); }

/* Machine Status card */
.machine-status-card { align-items: flex-start; }
.ms-icon { font-size: 26px; margin-bottom: 6px; }
.ms-label { font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: .06em; margin-bottom: 6px; }
.ms-status { font-size: 20px; font-weight: 900; letter-spacing: .04em; margin-bottom: 4px; }
.ms-online  { color: #00e5a0; }
.ms-offline { color: #ef4444; }
.ms-sub { font-size: 12px; color: var(--text-secondary); }

/* ── Section card ── */
.section-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 16px; margin-bottom: 16px;
}
.card-title-bar {
  font-size: 12px; font-weight: 700; color: var(--text-muted);
  letter-spacing: 0.8px; margin-bottom: 14px;
  display: flex; align-items: center; gap: 8px;
}
.title-sq {
  width: 10px; height: 10px; border-radius: 2px;
  background: var(--accent-blue); flex-shrink: 0;
}
.title-sq-yellow { background: var(--accent-yellow); }
.title-sq-red    { background: var(--accent-red); }

.card-header {
  display: flex; align-items: center;
  justify-content: space-between; margin-bottom: 14px;
}
.table-period-badge {
  font-size: 11px; font-weight: 600; color: #9ca3af;
  background: rgba(255,255,255,0.07); border: 1px solid var(--border);
  border-radius: 20px; padding: 3px 10px;
}
.pagination-bar {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 10px; margin-top: 14px; padding-top: 14px;
  border-top: 1px solid var(--border);
}
.pagination-label { font-size: 13px; color: #9ca3af; }
.pagination-controls { display: flex; align-items: center; gap: 8px; }
.pagination-page { font-size: 13px; color: #9ca3af; white-space: nowrap; }

/* ── Overview ── */
.dash-clock-row {
  display: flex; justify-content: flex-end;
  margin-bottom: 10px;
}
.overview-clock { font-size: 13px; color: var(--text-muted); }
.overview-cards {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
  margin-bottom: 16px;
}
.overview-card {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 12px; padding: 20px 22px;
}
.ov-icon { font-size: 26px; margin-bottom: 10px; }
.ov-label { font-size: 11px; font-weight: 600; color: var(--text-muted); letter-spacing: .06em; margin-bottom: 8px; }
.ov-count { font-size: 36px; font-weight: 800; color: var(--text-primary); line-height: 1; margin-bottom: 4px; }
.ov-sub { font-size: 12px; color: var(--text-muted); margin-bottom: 6px; }
.ov-pct { font-size: 13px; font-weight: 600; }
.pct-up   { color: #22c55e; }
.pct-down { color: #ef4444; }

/* ── Charts ── */
.charts-row {
  display: grid; grid-template-columns: 1fr 340px; gap: 16px; margin-bottom: 16px;
}
/* Grid/flex items default to min-width:auto, which refuses to shrink below the
   canvas's own intrinsic size — if Chart.js ever mis-measures on first mount
   (a known issue on some mobile browsers) that pushes the whole card, and the
   page, wider than the screen. min-width:0 lets the grid track actually
   respect its column size regardless of what the canvas reports. */
.chart-card-main, .chart-card-side { margin-bottom: 0; min-width: 0; }
.chart-wrap { height: 260px; position: relative; overflow: hidden; min-width: 0; }
.chart-wrap canvas { max-width: 100% !important; }
.donut-wrap { display: flex; align-items: center; justify-content: center; }
.chart-empty { color: var(--text-muted); font-size: 13px; text-align: center; padding-top: 80px; }

/* ── Breakdown + System Status row ── */
.breakdown-status-row {
  display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;
}
.sys-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
}
.sys-item {
  display: flex; align-items: center; gap: 10px;
  background: var(--bg-hover); border: 1px solid var(--border);
  border-radius: 8px; padding: 10px 14px;
}
.sys-dot {
  width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
}
.sys-dot-green  { background: #00e5a0; box-shadow: 0 0 6px #00e5a0aa; }
.sys-dot-yellow { background: #f59e0b; box-shadow: 0 0 6px #f59e0baa; }
.sys-dot-red    { background: #ef4444; box-shadow: 0 0 6px #ef4444aa; }
.sys-label { flex: 1; font-size: 13px; color: var(--text-primary); }
.sys-value { font-size: 12px; font-weight: 600; letter-spacing: .04em; }
.sys-val-green  { color: #00e5a0; }
.sys-val-yellow { color: #f59e0b; }
.sys-val-red    { color: #ef4444; }

/* ── Admin Controls ── */
.controls-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
.ctrl-card {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 16px; background: var(--bg-hover);
  border: 1px solid var(--border); border-radius: 8px;
  cursor: pointer; text-align: left; width: 100%;
  transition: border-color 0.2s, background 0.2s;
}
.ctrl-card:hover { background: var(--bg-secondary); border-color: var(--accent-blue); }
.ctrl-card-icon { font-size: 20px; padding: 8px; border-radius: 8px; flex-shrink: 0; }
.ctrl-blue   { background: rgba(78, 110, 242, 0.15); }
.ctrl-yellow { background: rgba(245, 158, 11, 0.15); }
.ctrl-purple { background: rgba(168, 85, 247, 0.15); }
.ctrl-gray   { background: rgba(107, 114, 128, 0.15); }
.ctrl-card-label { font-size: 14px; font-weight: 500; color: var(--text-primary); }

/* ── Reward Config ── */
.reward-materials {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 12px; margin-bottom: 16px;
}
.reward-mat-card {
  background: var(--bg-hover); border: 1px solid var(--border);
  border-radius: 8px; padding: 12px;
}
.reward-mat-label { font-size: 13px; color: var(--text-secondary); margin-bottom: 8px; }
.reward-mat-control { display: flex; gap: 8px; align-items: center; }
.reward-input {
  width: 70px; padding: 6px 8px;
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 6px; color: var(--text-primary); font-size: 16px;
  font-weight: 700; outline: none; text-align: center;
}
.update-btn {
  flex: 1; padding: 7px 12px;
  background: var(--accent-green); color: white;
  border: none; border-radius: 6px;
  cursor: pointer; font-size: 13px; font-weight: 600;
}
.update-btn:disabled { opacity: 0.6; cursor: not-allowed; }

.reward-stats-row {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;
  padding-top: 14px; border-top: 1px solid var(--border);
}
.reward-stat { text-align: center; }
.reward-stat-label { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
.reward-stat-value { font-size: 24px; font-weight: 800; color: var(--text-primary); }

/* ── Transaction log ── */
.confidence-wrap { display: flex; align-items: center; gap: 8px; min-width: 140px; }
.confidence-bar {
  flex: 1; height: 6px; background: var(--border); border-radius: 3px; overflow: hidden;
}
.confidence-fill {
  height: 100%; background: var(--accent-green); border-radius: 3px;
  transition: width 0.4s ease;
}
.confidence-pct { font-size: 12px; color: var(--text-muted); min-width: 32px; }

.compartment { font-weight: 700; color: var(--text-secondary); font-size: 13px; }

.badge-ok {
  display: inline-block; padding: 2px 10px; border-radius: 4px;
  font-size: 11px; font-weight: 700; letter-spacing: 0.5px;
  border: 1px solid var(--accent-green); color: var(--accent-green);
}
.badge-rejected {
  display: inline-block; padding: 2px 10px; border-radius: 4px;
  font-size: 11px; font-weight: 700; letter-spacing: 0.5px;
  background: var(--accent-red); color: white;
}

.item-cell { display: flex; align-items: center; gap: 6px; }

.filter-select {
  padding: 7px 10px; background: var(--bg-hover);
  border: 1px solid var(--border); border-radius: 6px;
  color: var(--text-primary); font-size: 13px; outline: none;
}

/* ── Alerts ── */
.alert-list { display: flex; flex-direction: column; gap: 8px; }
.alert-item {
  display: flex; align-items: center; gap: 10px; padding: 10px;
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px;
}
.alert-icon { font-size: 18px; }
.alert-bins { display: flex; gap: 6px; flex-wrap: wrap; margin-left: 4px; }
.bin-tag {
  background: var(--accent-red); color: white;
  padding: 2px 8px; border-radius: 4px;
  font-size: 11px; font-weight: 600;
}

/* ── Table ── */
.table-wrap { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table th {
  text-align: left; padding: 10px 12px;
  border-bottom: 2px solid var(--border);
  color: var(--text-muted); font-size: 11px;
  font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px;
}
.data-table td {
  padding: 10px 12px; border-bottom: 1px solid var(--border);
  color: var(--text-primary); vertical-align: middle;
}
.data-table tr:hover td { background: var(--bg-hover); }
.empty-cell { text-align: center; color: var(--text-muted); padding: 30px; font-size: 13px; }

/* ── Users ── */
.user-cell { display: flex; align-items: center; gap: 8px; }
.user-avatar-sm {
  width: 26px; height: 26px; border-radius: 50%;
  background: var(--grad-header); color: white;
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 700; flex-shrink: 0;
}

/* ── Machines ── */
.machines-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px;
}
.machine-admin-card {
  background: var(--bg-hover); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 14px;
}
.machine-admin-header {
  display: flex; align-items: flex-start;
  justify-content: space-between; margin-bottom: 6px;
}
.machine-code-badge {
  display: inline-block; background: var(--bg-card);
  padding: 2px 8px; border-radius: 4px;
  font-size: 11px; font-family: monospace;
  color: var(--text-muted); margin-left: 8px;
}
.machine-loc { font-size: 12px; color: var(--text-muted); margin-bottom: 12px; }
.bin-admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
.bin-admin { display: flex; flex-direction: column; gap: 3px; }
.bin-admin span { font-size: 11px; color: var(--text-secondary); }
.bin-bar-sm { height: 4px; background: var(--border); border-radius: 2px; }
.bin-fill-sm { height: 100%; border-radius: 2px; transition: width 0.5s; }
.bin-ok      { background: var(--accent-green); }
.bin-warning { background: var(--accent-yellow); }
.bin-danger  { background: var(--accent-red); }
.text-red    { color: var(--accent-red) !important; }
.text-muted-sm { color: var(--text-muted) !important; font-size: 11px; }
.machine-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.empty-machines {
  grid-column: 1 / -1; text-align: center;
  color: var(--text-muted); padding: 40px;
}
.empty-machines p { margin-bottom: 14px; }

/* ── Badges ── */
.status-badge {
  padding: 2px 8px; border-radius: 4px;
  font-size: 11px; font-weight: 600;
}
.status-active     { background: rgba(34, 197, 94, 0.15); color: var(--accent-green); }
.status-completed  { background: rgba(78, 110, 242, 0.15); color: var(--accent-blue); }
.status-cancelled  { background: rgba(239, 68, 68, 0.15); color: var(--accent-red); }
.status-inactive   { background: rgba(107, 114, 128, 0.15); color: var(--text-muted); }
.status-maintenance{ background: rgba(245, 158, 11, 0.15); color: var(--accent-yellow); }
.role-badge { padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
.role-admin { background: rgba(168, 85, 247, 0.15); color: var(--accent-purple); }
.role-user  { background: rgba(78, 110, 242, 0.15); color: var(--accent-blue); }

/* ── Buttons ── */
.action-btn {
  padding: 4px 10px; border-radius: 4px;
  border: 1px solid var(--border); background: var(--bg-hover);
  color: var(--text-secondary); cursor: pointer;
  font-size: 12px; margin-right: 4px;
}
.edit-btn { border-color: var(--accent-blue); color: var(--accent-blue); }
.del-btn  { border-color: var(--accent-red);  color: var(--accent-red);  }
.add-btn  {
  padding: 8px 16px; background: var(--accent-green); color: white;
  border: none; border-radius: 6px; cursor: pointer;
  font-size: 13px; font-weight: 600;
}
.search-input {
  padding: 7px 12px; background: var(--bg-hover);
  border: 1px solid var(--border); border-radius: 6px;
  color: var(--text-primary); font-size: 13px; outline: none; width: 200px;
}

/* ── Modal ── */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.55);
  display: flex; align-items: center; justify-content: center; z-index: 100;
}
.modal {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 12px; padding: 24px; width: 400px; max-width: 95vw;
  max-height: 90vh; overflow-y: auto;
}
.modal h3 { font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 16px; }
.form-group { margin-bottom: 12px; }
.form-group label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 5px; }
.form-group input,
.form-group select {
  width: 100%; padding: 9px 12px; box-sizing: border-box;
  background: var(--bg-hover); border: 1px solid var(--border);
  border-radius: 6px; color: var(--text-primary); font-size: 14px; outline: none;
}
.input-disabled { opacity: 0.5; cursor: not-allowed; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.form-error { font-size: 12px; color: var(--accent-red); margin: 6px 0 0; }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }

/* ── Confirm modal ── */
.confirm-modal { width: 340px; }
.confirm-message { font-size: 14px; color: var(--text-primary); line-height: 1.5; margin: 0; }
.confirm-modal .action-btn { padding: 8px 16px; font-size: 13px; }
.confirm-modal .del-btn  { background: rgba(239,68,68,0.12); }
.confirm-modal .edit-btn { background: rgba(78,110,242,0.12); }

/* ── Toast ── */
.toast {
  position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
  display: flex; align-items: center; gap: 8px;
  background: var(--bg-card); border: 1px solid var(--border);
  border-left: 4px solid var(--accent-green);
  color: var(--text-primary); padding: 12px 18px;
  border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,0.35);
  font-size: 13.5px; z-index: 300; max-width: 90vw;
}
.toast.error { border-left-color: var(--accent-red); }
.toast-icon { font-size: 14px; flex-shrink: 0; }
.toast-fade-enter-active, .toast-fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translate(-50%, 8px); }
@media (prefers-reduced-motion: reduce) {
  .toast-fade-enter-active, .toast-fade-leave-active { transition: none; }
}

/* ── Loading ── */
.loading-overlay { display: flex; justify-content: center; align-items: center; padding: 60px; }
.spinner-lg {
  width: 40px; height: 40px; border: 3px solid var(--border);
  border-top-color: var(--accent-blue); border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Error banner ── */
.error-banner {
  display: flex; align-items: center; justify-content: space-between;
  background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);
  color: var(--accent-red); padding: 10px 24px;
  font-size: 13px; gap: 10px;
}
.error-close {
  background: none; border: none; color: var(--accent-red);
  cursor: pointer; font-size: 16px; line-height: 1;
}

/* ── Util ── */
.mono   { font-family: monospace; }
.small  { font-size: 11px; }
.muted  { color: var(--text-muted); }
.pts-green { color: var(--accent-green); font-weight: 600; }

.mobile-menu-btn {
  display: none; background: none; border: none;
  color: var(--text-primary); font-size: 20px; cursor: pointer; padding: 4px;
}
.mobile-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.5); z-index: 199;
}

/* ── Responsive ── */

/* Tablet: ≤ 1024px */
@media (max-width: 1024px) {
  .stats-grid       { grid-template-columns: repeat(4, 1fr); }
  .overview-cards   { grid-template-columns: repeat(2, 1fr); }
  .charts-row       { grid-template-columns: 1fr; }
  .chart-card-side  { min-height: 260px; }
  .breakdown-status-row { grid-template-columns: 1fr; }
  .reward-materials { grid-template-columns: repeat(2, 1fr); }
  .sys-grid         { grid-template-columns: 1fr 1fr; }
}

/* Large mobile: ≤ 768px */
@media (max-width: 768px) {
  .mobile-menu-btn { display: block; }
  .mobile-overlay  { display: block; }
  .sidebar {
    position: fixed; left: 0; top: 0; bottom: 0; z-index: 200;
    transform: translateX(-100%); transition: transform .25s;
    width: 220px !important;
  }
  .sidebar.collapsed                { transform: translateX(-100%); width: 220px !important; }
  .sidebar.mobile-open,
  .sidebar.collapsed.mobile-open   { transform: translateX(0) !important; width: 220px !important; }
  .admin-main          { margin-left: 0 !important; }

  .stats-grid       { grid-template-columns: repeat(2, 1fr); }
  .overview-cards   { grid-template-columns: repeat(2, 1fr); }
  .controls-grid    { grid-template-columns: 1fr 1fr; }
  .reward-materials { grid-template-columns: repeat(2, 1fr); }
  .sys-grid         { grid-template-columns: 1fr; }
  .form-row         { grid-template-columns: 1fr; }
  .charts-row       { grid-template-columns: 1fr; }
  .breakdown-status-row { grid-template-columns: 1fr; }

  .admin-topbar     { flex-wrap: wrap; gap: 8px; }
  .topbar-right     { flex-wrap: wrap; }
  .table-wrap       { overflow-x: auto; }
}

/* Small mobile: ≤ 480px */
@media (max-width: 480px) {
  .stats-grid       { grid-template-columns: repeat(2, 1fr); }
  .overview-cards   { grid-template-columns: 1fr 1fr; }
  .ov-count         { font-size: 26px; }
  .controls-grid    { grid-template-columns: 1fr; }
  .reward-materials { grid-template-columns: 1fr 1fr; }
  .section-card     { padding: 12px; }
  .admin-topbar     { padding: 10px 12px; }
  .tab-content      { padding: 12px; }
}
</style>
