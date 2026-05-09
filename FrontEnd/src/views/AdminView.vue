<template>
  <div class="admin-page">
    <!-- Sidebar -->
    <aside :class="['sidebar', { collapsed: sidebarCollapsed }]">
      <div class="sidebar-header">
        <span class="sidebar-logo">♻️</span>
        <span class="sidebar-title" v-if="!sidebarCollapsed">RVM Admin</span>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          {{ sidebarCollapsed ? '→' : '←' }}
        </button>
      </div>
      <nav class="sidebar-nav">
        <button v-for="item in navItems" :key="item.id" :class="['nav-item', { active: activeTab === item.id }]"
          @click="activeTab = item.id; fetchTabData(item.id)">
          <span class="nav-icon">{{ item.icon }}</span>
          <span class="nav-label" v-if="!sidebarCollapsed">{{ item.label }}</span>
        </button>
      </nav>
      <div class="sidebar-footer" v-if="!sidebarCollapsed">
        <div class="admin-info">
          <div class="admin-avatar">A</div>
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
        <h2 class="page-title">{{ currentNavItem?.label }}</h2>
        <div class="topbar-right">
          <span class="last-updated">Last updated: {{ lastUpdated }}</span>
          <button class="refresh-btn" @click="fetchTabData(activeTab)">🔄 Refresh</button>
        </div>
      </div>

      <!-- DASHBOARD tab -->
      <div v-if="activeTab === 'dashboard'" class="tab-content">
        <div v-if="loadingStats" class="loading-overlay">
          <div class="spinner-lg"></div>
        </div>
        <div v-else>
          <!-- Stats grid -->
          <div class="stats-grid">
            <div class="stat-card" v-for="stat in statsCards" :key="stat.label">
              <div class="stat-icon">{{ stat.icon }}</div>
              <div class="stat-info">
                <div class="stat-value">{{ stat.value }}</div>
                <div class="stat-label">{{ stat.label }}</div>
              </div>
            </div>
          </div>

          <!-- Bin alerts -->
          <div class="section-card" v-if="fullBins.length">
            <h3 class="card-title">⚠️ Bin Alerts (Bins ≥ 90%)</h3>
            <div class="alert-list">
              <div v-for="machine in fullBins" :key="machine.id" class="alert-item">
                <span class="alert-icon">🚨</span>
                <strong>{{ machine.name }}</strong>
                <div class="alert-bins">
                  <span v-if="machine.aluminum_level >= 90" class="bin-tag">Aluminum {{ machine.aluminum_level
                  }}%</span>
                  <span v-if="machine.plastic_level >= 90" class="bin-tag">Plastic {{ machine.plastic_level }}%</span>
                  <span v-if="machine.glass_level >= 90" class="bin-tag">Glass {{ machine.glass_level }}%</span>
                  <span v-if="machine.paper_level >= 90" class="bin-tag">Paper {{ machine.paper_level }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Material stats -->
          <div class="section-card" v-if="materialStats.length">
            <h3 class="card-title">📊 Material Breakdown</h3>
            <div class="material-stats">
              <div v-for="mat in materialStats" :key="mat.material_selected" class="mat-stat">
                <div class="mat-row">
                  <span class="mat-name">{{ getMaterialIcon(mat.material_selected) }} {{ mat.material_selected }}</span>
                  <span class="mat-count">{{ mat.count }} items</span>
                  <span class="mat-weight">{{ (mat.total_weight / 1000).toFixed(2) }} kg</span>
                  <span class="mat-pts">{{ mat.total_points }} pts</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent sessions -->
          <div class="section-card">
            <h3 class="card-title">🕒 Recent Sessions</h3>
            <div class="table-wrap">
              <table class="data-table">
                <thead>
                  <tr>
                    <th>Session Code</th>
                    <th>User</th>
                    <th>Machine</th>
                    <th>Status</th>
                    <th>Points</th>
                    <th>Started</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="s in recentSessions" :key="s.session_code">
                    <td class="mono">{{ s.session_code }}</td>
                    <td>{{ s.user_name }}</td>
                    <td>{{ s.machine_name }}</td>
                    <td><span :class="['status-badge', 'status-' + s.status]">{{ s.status }}</span></td>
                    <td class="pts-green">+{{ s.points_earned }}</td>
                    <td class="muted">{{ formatDate(s.started_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- USERS tab -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div v-if="loadingUsers" class="loading-overlay">
          <div class="spinner-lg"></div>
        </div>
        <div v-else class="section-card">
          <div class="card-header">
            <h3 class="card-title">👥 All Users</h3>
            <input v-model="userSearch" placeholder="Search users..." class="search-input" />
          </div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email/Phone</th>
                  <th>Points</th>
                  <th>Role</th>
                  <th>Verified</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <td class="muted">{{ user.id }}</td>
                  <td>
                    <div class="user-cell">
                      <div class="user-avatar-sm">{{ user.name?.charAt(0) }}</div>
                      {{ user.name }}
                    </div>
                  </td>
                  <td class="muted">{{ user.email || user.phone }}</td>
                  <td class="pts-green">{{ user.total_points }}</td>
                  <td><span :class="['role-badge', 'role-' + user.role]">{{ user.role }}</span></td>
                  <td>{{ user.is_verified ? '✅' : '❌' }}</td>
                  <td>
                    <button class="action-btn edit-btn" @click="editUser(user)">Edit</button>
                    <button class="action-btn del-btn" @click="deleteUser(user.id)">Del</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- MACHINES tab -->
      <div v-if="activeTab === 'machines'" class="tab-content">
        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title">🏭 RVM Machines</h3>
            <button class="add-btn" @click="showAddMachine = true">+ Add Machine</button>
          </div>
          <div v-if="loadingMachines" class="loading-overlay">
            <div class="spinner-lg"></div>
          </div>
          <div v-else class="machines-grid">
            <div v-for="machine in adminMachines" :key="machine.id" class="machine-admin-card">
              <div class="machine-admin-header">
                <div>
                  <strong>{{ machine.name }}</strong>
                  <span class="machine-code-badge">{{ machine.machine_code }}</span>
                </div>
                <span :class="['status-badge', 'status-' + machine.status]">{{ machine.status }}</span>
              </div>
              <p class="machine-loc">📍 {{ machine.location_name }}</p>
              <div class="bin-admin-grid">
                <div v-for="bin in binTypes" :key="bin.id" class="bin-admin">
                  <span>{{ bin.icon }} {{ bin.label }}</span>
                  <div class="bin-bar-sm">
                    <div :class="['bin-fill-sm', getBinClass(machine[bin.id + '_level'])]"
                      :style="{ width: machine[bin.id + '_level'] + '%' }"></div>
                  </div>
                  <span :class="machine[bin.id + '_level'] >= 90 ? 'text-red' : 'text-muted'">
                    {{ machine[bin.id + '_level'] }}%
                  </span>
                </div>
              </div>
              <div class="machine-actions">
                <button class="action-btn edit-btn" @click="editMachine(machine)">Edit</button>
                <button class="action-btn" @click="resetBins(machine)">Reset Bins</button>
                <button class="action-btn del-btn" @click="deleteMachine(machine.id)">Delete</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- SESSIONS tab -->
      <div v-if="activeTab === 'sessions'" class="tab-content">
        <div class="section-card">
          <h3 class="card-title">📋 All Sessions</h3>
          <div v-if="loadingSessions" class="loading-overlay">
            <div class="spinner-lg"></div>
          </div>
          <div v-else class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>User</th>
                  <th>Machine</th>
                  <th>Status</th>
                  <th>Items</th>
                  <th>Points</th>
                  <th>Started</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in sessions" :key="s.id">
                  <td class="mono small">{{ s.session_code }}</td>
                  <td>{{ s.user?.name }}</td>
                  <td>{{ s.machine?.name }}</td>
                  <td><span :class="['status-badge', 'status-' + s.status]">{{ s.status }}</span></td>
                  <td>{{ s.total_items }}</td>
                  <td class="pts-green">+{{ s.points_earned }}</td>
                  <td class="muted small">{{ formatDate(s.started_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </main>

    <!-- Edit User Modal -->
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
        <div class="modal-actions">
          <button class="action-btn" @click="editingUser = null">Cancel</button>
          <button class="action-btn edit-btn" @click="saveUser">Save</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, inject, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'

const router = useRouter()
const auth = useAuthStore()
const theme = inject('theme')
const toggleTheme = inject('toggleTheme')

const activeTab = ref('dashboard')
const sidebarCollapsed = ref(false)
const lastUpdated = ref('—')
const loadingStats = ref(false)
const loadingUsers = ref(false)
const loadingMachines = ref(false)
const loadingSessions = ref(false)
const userSearch = ref('')
const editingUser = ref(null)
const showAddMachine = ref(false)

const statsData = ref({})
const users = ref([])
const adminMachines = ref([])
const sessions = ref([])
const materialStats = ref([])
const recentSessions = ref([])
const fullBins = ref([])

const navItems = [
  { id: 'dashboard', icon: '📊', label: 'Dashboard' },
  { id: 'users', icon: '👥', label: 'Users' },
  { id: 'machines', icon: '🏭', label: 'Machines' },
  { id: 'sessions', icon: '📋', label: 'Sessions' },
]

const binTypes = [
  { id: 'aluminum', icon: '🥫', label: 'Aluminum' },
  { id: 'plastic', icon: '🧴', label: 'Plastic' },
  { id: 'glass', icon: '🍶', label: 'Glass' },
  { id: 'paper', icon: '📄', label: 'Paper' },
]

const currentNavItem = computed(() => navItems.find(n => n.id === activeTab.value))

const statsCards = computed(() => [
  { icon: '👥', label: 'Total Users', value: statsData.value.total_users || 0 },
  { icon: '🏭', label: 'Total Machines', value: statsData.value.total_machines || 0 },
  { icon: '🔄', label: 'Active Sessions', value: statsData.value.active_sessions || 0 },
  { icon: '⚖️', label: 'Total Weight (kg)', value: statsData.value.total_weight_kg || 0 },
  { icon: '⭐', label: 'Points Distributed', value: statsData.value.total_points_given || 0 },
  { icon: '📦', label: 'Transactions', value: statsData.value.total_transactions || 0 },
])

const filteredUsers = computed(() => {
  if (!userSearch.value) return users.value
  const q = userSearch.value.toLowerCase()
  return users.value.filter(u =>
    u.name?.toLowerCase().includes(q) || u.email?.toLowerCase().includes(q)
  )
})

function getMaterialIcon(mat) { return { aluminum: '🥫', plastic: '🧴', glass: '🍶', paper: '📄' }[mat] || '♻️' }
function getBinClass(level) { if (level >= 90) return 'bin-danger'; if (level >= 70) return 'bin-warning'; return 'bin-ok'; }
function formatDate(ts) { return ts ? new Date(ts).toLocaleDateString() + ' ' + new Date(ts).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '—' }

async function fetchTabData(tab) {
  lastUpdated.value = new Date().toLocaleTimeString()
  try {
    if (tab === 'dashboard') {
      loadingStats.value = true
      const res = await api.get('/admin/stats')
      statsData.value = res.data.stats
      materialStats.value = res.data.stats.material_stats || []
      recentSessions.value = res.data.stats.recent_sessions || []
      fullBins.value = res.data.stats.full_bins || []
    } else if (tab === 'users') {
      loadingUsers.value = true
      const res = await api.get('/admin/users')
      users.value = res.data.users?.data || []
    } else if (tab === 'machines') {
      loadingMachines.value = true
      const res = await api.get('/admin/machines')
      adminMachines.value = res.data.machines || []
    } else if (tab === 'sessions') {
      loadingSessions.value = true
      const res = await api.get('/admin/sessions')
      sessions.value = res.data.sessions?.data || []
    }
  } catch {
    // Silently fail — show empty states
  } finally {
    loadingStats.value = loadingUsers.value = loadingMachines.value = loadingSessions.value = false
  }
}

function editUser(user) { editingUser.value = { ...user } }

async function saveUser() {
  try {
    await api.put(`/admin/users/${editingUser.value.id}`, editingUser.value)
    const idx = users.value.findIndex(u => u.id === editingUser.value.id)
    if (idx > -1) users.value[idx] = { ...editingUser.value }
    editingUser.value = null
  } catch { }
}

async function deleteUser(id) {
  if (!confirm('Delete this user?')) return
  try {
    await api.delete(`/admin/users/${id}`)
    users.value = users.value.filter(u => u.id !== id)
  } catch { }
}

function editMachine(machine) { /* implement edit modal */ }

async function deleteMachine(id) {
  if (!confirm('Delete this machine?')) return
  try {
    await api.delete(`/admin/machines/${id}`)
    adminMachines.value = adminMachines.value.filter(m => m.id !== id)
  } catch { }
}

async function resetBins(machine) {
  try {
    await api.put(`/admin/machines/${machine.id}/bin-levels`, { aluminum_level: 0, plastic_level: 0, glass_level: 0, paper_level: 0 })
    machine.aluminum_level = machine.plastic_level = machine.glass_level = machine.paper_level = 0
  } catch { }
}

async function handleLogout() { await auth.logout(); router.push('/') }

onMounted(() => fetchTabData('dashboard'))
</script>

<style scoped>
.admin-page {
  display: flex;
  min-height: 100vh;
  background: var(--bg-primary);
}

/* Sidebar */
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

.sidebar.collapsed {
  width: 60px;
}

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 12px;
  border-bottom: 1px solid var(--border);
}

.sidebar-logo {
  font-size: 20px;
  flex-shrink: 0;
}

.sidebar-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
}

.collapse-btn {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 14px;
}

.sidebar-nav {
  flex: 1;
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 8px;
  border-radius: 8px;
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
  width: 100%;
  text-align: left;
}

.nav-item:hover {
  background: var(--bg-card);
  color: var(--text-primary);
}

.nav-item.active {
  background: rgba(78, 110, 242, 0.15);
  color: var(--accent-blue);
  font-weight: 600;
}

.nav-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.nav-label {
  white-space: nowrap;
  overflow: hidden;
}

.sidebar-footer {
  padding: 12px;
  border-top: 1px solid var(--border);
}

.admin-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.admin-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--grad-header);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 14px;
}

.admin-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-primary);
}

.admin-role {
  font-size: 11px;
  color: var(--text-muted);
}

.sidebar-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.ctrl-btn {
  background: var(--bg-card);
  border: 1px solid var(--border);
  color: var(--text-secondary);
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  text-decoration: none;
  text-align: center;
}

.logout-btn-sm {
  background: none;
  border: 1px solid var(--accent-red);
  color: var(--accent-red);
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
}

/* Main */
.admin-main {
  flex: 1;
  overflow: auto;
}

.admin-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border);
  position: sticky;
  top: 0;
  z-index: 5;
}

.page-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-primary);
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 12px;
}

.last-updated {
  font-size: 12px;
  color: var(--text-muted);
}

.refresh-btn {
  background: var(--bg-card);
  border: 1px solid var(--border);
  color: var(--text-secondary);
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
}

.tab-content {
  padding: 20px 24px;
}

/* Stats grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 14px;
  margin-bottom: 20px;
}

.stat-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.stat-icon {
  font-size: 24px;
}

.stat-value {
  font-size: 22px;
  font-weight: 800;
  color: var(--text-primary);
}

.stat-label {
  font-size: 12px;
  color: var(--text-muted);
}

.section-card {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px;
  margin-bottom: 16px;
}

.card-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 14px;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

/* Alerts */
.alert-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.alert-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  background: rgba(239, 68, 68, 0.05);
  border: 1px solid rgba(239, 68, 68, 0.2);
  border-radius: 8px;
}

.alert-icon {
  font-size: 18px;
}

.alert-bins {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-left: 4px;
}

.bin-tag {
  background: var(--accent-red);
  color: white;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

/* Material stats */
.mat-row {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px 0;
  border-bottom: 1px solid var(--border);
}

.mat-row:last-child {
  border-bottom: none;
}

.mat-name {
  flex: 1;
  font-size: 14px;
  color: var(--text-primary);
  text-transform: capitalize;
}

.mat-count,
.mat-weight,
.mat-pts {
  font-size: 13px;
  color: var(--text-secondary);
  min-width: 80px;
  text-align: right;
}

/* Table */
.table-wrap {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.data-table th {
  text-align: left;
  padding: 10px 12px;
  border-bottom: 2px solid var(--border);
  color: var(--text-muted);
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.data-table td {
  padding: 10px 12px;
  border-bottom: 1px solid var(--border);
  color: var(--text-primary);
  vertical-align: middle;
}

.data-table tr:hover td {
  background: var(--bg-hover);
}

.mono {
  font-family: monospace;
}

.small {
  font-size: 11px;
}

.muted {
  color: var(--text-muted);
}

.pts-green {
  color: var(--accent-green);
  font-weight: 600;
}

.status-badge {
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

.status-active {
  background: rgba(34, 197, 94, 0.15);
  color: var(--accent-green);
}

.status-completed {
  background: rgba(78, 110, 242, 0.15);
  color: var(--accent-blue);
}

.status-cancelled {
  background: rgba(239, 68, 68, 0.15);
  color: var(--accent-red);
}

.status-inactive {
  background: rgba(107, 114, 128, 0.15);
  color: var(--text-muted);
}

.status-maintenance {
  background: rgba(245, 158, 11, 0.15);
  color: var(--accent-yellow);
}

.role-badge {
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-weight: 600;
}

.role-admin {
  background: rgba(168, 85, 247, 0.15);
  color: var(--accent-purple);
}

.role-user {
  background: rgba(78, 110, 242, 0.15);
  color: var(--accent-blue);
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.user-avatar-sm {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: var(--grad-header);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
}

.action-btn {
  padding: 4px 10px;
  border-radius: 4px;
  border: 1px solid var(--border);
  background: var(--bg-hover);
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 12px;
  margin-right: 4px;
}

.edit-btn {
  border-color: var(--accent-blue);
  color: var(--accent-blue);
}

.del-btn {
  border-color: var(--accent-red);
  color: var(--accent-red);
}

.add-btn {
  padding: 8px 16px;
  background: var(--accent-green);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
}

.search-input {
  padding: 8px 12px;
  background: var(--bg-hover);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text-primary);
  font-size: 13px;
  outline: none;
  width: 200px;
}

/* Machines grid */
.machines-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 14px;
}

.machine-admin-card {
  background: var(--bg-hover);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 14px;
}

.machine-admin-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 6px;
}

.machine-code-badge {
  display: inline-block;
  background: var(--bg-card);
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 11px;
  font-family: monospace;
  color: var(--text-muted);
  margin-left: 8px;
}

.machine-loc {
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 12px;
}

.bin-admin-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  margin-bottom: 12px;
}

.bin-admin {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.bin-admin span {
  font-size: 11px;
  color: var(--text-secondary);
}

.bin-bar-sm {
  height: 4px;
  background: var(--border);
  border-radius: 2px;
}

.bin-fill-sm {
  height: 100%;
  border-radius: 2px;
  transition: width 0.5s;
}

.bin-ok {
  background: var(--accent-green);
}

.bin-warning {
  background: var(--accent-yellow);
}

.bin-danger {
  background: var(--accent-red);
}

.text-red {
  color: var(--accent-red) !important;
}

.text-muted {
  color: var(--text-muted) !important;
}

.machine-actions {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
}

.modal {
  background: var(--bg-card);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px;
  width: 360px;
}

.modal h3 {
  font-size: 16px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 16px;
}

.form-group {
  margin-bottom: 12px;
}

.form-group label {
  display: block;
  font-size: 12px;
  color: var(--text-muted);
  margin-bottom: 5px;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 9px 12px;
  background: var(--bg-hover);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text-primary);
  font-size: 14px;
  outline: none;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
  margin-top: 16px;
}

/* Loading */
.loading-overlay {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 60px;
}

.spinner-lg {
  width: 40px;
  height: 40px;
  border: 3px solid var(--border);
  border-top-color: var(--accent-blue);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
