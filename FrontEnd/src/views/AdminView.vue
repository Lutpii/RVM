<template>
  <div class="admin-page">
    <!-- Mobile overlay -->
    <div v-if="mobileSidebarOpen" class="mobile-overlay" @click="mobileSidebarOpen = false"></div>

    <!-- Sidebar -->
    <aside :class="['sidebar', { collapsed: sidebarCollapsed, 'mobile-open': mobileSidebarOpen }]">
      <div class="sidebar-header">
        <PhRecycle class="sidebar-logo" weight="regular" aria-hidden="true" />
        <span class="sidebar-title">RVM Admin</span>
      </div>
      <nav class="sidebar-nav">
        <button v-for="item in navItems" :key="item.id"
          :class="['nav-item', { active: activeTab === item.id }]"
          @click="switchTab(item.id)">
          <component :is="item.icon" class="nav-icon" weight="regular" aria-hidden="true" />
          <span class="nav-label">{{ item.id === 'detection' ? $t('admin.detectionReview.title') : item.label }}</span>
        </button>
      </nav>
      <div class="sidebar-footer">
        <div class="admin-info">
          <div class="admin-avatar">{{ auth.user?.name?.charAt(0) || 'A' }}</div>
          <div>
            <div class="admin-name">{{ auth.user?.name }}</div>
            <div class="admin-role">Administrator</div>
          </div>
        </div>
        <div class="sidebar-actions">
          <button class="ctrl-btn" @click="toggleTheme()" :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'">
            <PhSun v-if="theme === 'dark'" weight="regular" aria-hidden="true" />
            <PhMoon v-else weight="regular" aria-hidden="true" />
          </button>
          <RouterLink to="/dashboard" class="ctrl-btn"><PhUser weight="regular" aria-hidden="true" /> User View</RouterLink>
          <button class="logout-btn-sm" @click="handleLogout">Logout</button>
        </div>
      </div>

      <!-- Floating edge toggle — sits on the sidebar/content border so it stays
           in the same fixed spot regardless of collapsed state, instead of
           living inside the header where it used to get squeezed among the
           icon-only nav once collapsed. Desktop only (hidden on mobile below;
           the mobile drawer is a full-width overlay, not a collapsible rail). -->
      <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed"
        :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'">
        <PhCaretLeft v-if="!sidebarCollapsed" weight="bold" aria-hidden="true" />
        <PhCaretRight v-else weight="bold" aria-hidden="true" />
      </button>
    </aside>

    <!-- Main content -->
    <main class="admin-main">
      <div class="admin-topbar">
        <div style="display:flex;align-items:center;gap:10px">
          <button class="mobile-menu-btn" @click="mobileSidebarOpen = !mobileSidebarOpen; sidebarCollapsed = false" aria-label="Toggle sidebar menu">
            <PhList weight="regular" aria-hidden="true" />
          </button>
          <h2 class="page-title">{{ currentNavItem?.id === 'detection' ? $t('admin.detectionReview.title') : currentNavItem?.label }}</h2>
        </div>
        <div class="topbar-right">
          <span class="live-dot" v-if="isLive"></span>
          <span class="last-updated">Updated: {{ lastUpdated }}</span>
          <button class="refresh-btn" @click="fetchTabData(activeTab, true)"><PhArrowsClockwise weight="regular" aria-hidden="true" /> Refresh</button>
        </div>
      </div>

      <!-- Only this area scrolls — topbar above stays put -->
      <div class="admin-scroll-area">

      <!-- Global error banner -->
      <div v-if="tabError" class="error-banner">
        <PhWarning weight="regular" aria-hidden="true" /> {{ tabError }}
        <button class="error-close" @click="tabError = ''" aria-label="Dismiss error">
          <PhX weight="regular" aria-hidden="true" />
        </button>
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
              <svg v-if="materialIconSvg(mat.key)" class="ov-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(mat.key)"></svg>
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
              <component :is="stat.icon" class="stat-icon" weight="regular" aria-hidden="true" />
              <div class="stat-info">
                <div class="stat-value" :style="stat.color ? { color: stat.color } : {}">{{ stat.value }}</div>
                <div class="stat-label">{{ stat.label }}</div>
              </div>
            </div>
            <!-- Machine Status card -->
            <div class="stat-card machine-status-card">
              <div>
                <PhRobot class="ms-icon" weight="regular" aria-hidden="true" />
                <div class="ms-label">MACHINE STATUS</div>
                <!-- From kiosk heartbeats; "NO DATA" when no machine has ever reported (A02). -->
                <template v-if="machineSummary.anyReported">
                  <div :class="['ms-status', machineSummary.online > 0 ? 'ms-online' : 'ms-offline']">
                    {{ machineSummary.online > 0 ? 'ONLINE' : 'OFFLINE' }}
                  </div>
                  <div class="ms-sub">{{ machineSummary.online }} of {{ machineSummary.total }} machines online</div>
                </template>
                <template v-else>
                  <div class="ms-status ms-nodata">NO DATA</div>
                  <div class="ms-sub">No machine has reported a heartbeat yet ({{ activeMachines }} of {{ adminMachines.length }} registered as active)</div>
                </template>
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

          <!-- Machine alerts: hardware faults and machines that stopped sending heartbeats (A05) -->
          <div class="section-card" v-if="machineAlerts.length">
            <h3 class="card-title-bar"><span class="title-sq title-sq-red"></span> MACHINE ALERTS</h3>
            <div class="alert-list">
              <div v-for="alert in machineAlerts" :key="alert.type + '-' + alert.machineId" class="alert-item">
                <PhWarningOctagon v-if="alert.type === 'fault'" class="alert-icon" weight="regular" aria-hidden="true" />
                <PhWifiSlash v-else class="alert-icon" weight="regular" aria-hidden="true" />
                <strong>{{ alert.name }}</strong>
                <div class="alert-bins">
                  <span class="bin-tag">{{ alert.type === 'fault' ? 'Hardware fault' : 'Offline' }}</span>
                  <span v-if="alert.detail" class="alert-detail">{{ alert.detail }}</span>
                  <span v-if="alert.since" class="alert-detail">
                    {{ alert.type === 'fault' ? 'since' : 'last seen' }} {{ relativeTime(alert.since) }}
                  </span>
                </div>
                <!-- actionable alert (A05): log the repair or jump to the machine -->
                <div class="alert-actions">
                  <button type="button" class="action-btn" @click="openHistoryFor(alert.machineId)">Service history</button>
                  <button type="button" class="action-btn" @click="switchTab('machines')">View machine</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Bin alerts -->
          <div class="section-card" v-if="fullBins.length">
            <h3 class="card-title-bar"><span class="title-sq title-sq-red"></span> BIN ALERTS (≥ 90%)</h3>
            <div class="alert-list">
              <div v-for="machine in fullBins" :key="machine.id" class="alert-item">
                <PhWarningOctagon class="alert-icon" weight="regular" aria-hidden="true" />
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
                      <th>Material</th><th>Items</th><th>Carbon Saved</th><th>Points</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="mat in materialStats" :key="mat.material_selected">
                      <td>
                        <svg v-if="materialIconSvg(mat.material_selected)" class="material-icon-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(mat.material_selected)"></svg>
                        {{ capitalize(mat.material_selected) }}
                      </td>
                      <td>{{ mat.count }}</td>
                      <td>{{ mat.total_carbon_kg.toFixed(3) }} kg</td>
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
              <h3 class="card-title-bar"><span class="title-sq"></span> <PhGear weight="regular" aria-hidden="true" /> SYSTEM STATUS</h3>
              <div class="sys-grid">
                <div v-for="item in systemStatusItems" :key="item.label" class="sys-item">
                  <span :class="['sys-dot', 'sys-dot-' + item.color]"></span>
                  <span class="sys-label">{{ item.label }}</span>
                  <span :class="['sys-value', 'sys-val-' + item.color]">{{ item.value }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Formal Report -->
          <div class="section-card">
            <h3 class="card-title-bar"><span class="title-sq"></span> FORMAL REPORT</h3>
            <div class="filter-toolbar formal-report-toolbar">
              <div class="date-range">
                <label class="date-field picker-field" @click="openDatePicker">
                  <span>From</span>
                <input v-model="reportDateFrom" type="date" />
                </label>
                <label class="date-field picker-field" @click="openDatePicker">
                  <span>To</span>
                <input v-model="reportDateTo" type="date" />
                </label>
              </div>
              <div class="filter-actions formal-report-actions">
                <button class="action-btn filter-action" @click="exportExcel"><PhDownloadSimple weight="regular" aria-hidden="true" /> Download Report</button>
                <!-- Email sending disabled for now -->
                <button class="action-btn edit-btn filter-action" disabled><PhEnvelopeSimple weight="regular" aria-hidden="true" /> Send via Email</button>
              </div>
            </div>
          </div>

          <!-- Admin Controls -->
          <div class="section-card">
            <h3 class="card-title-bar"><span class="title-sq"></span> ADMIN CONTROLS</h3>
            <div class="controls-grid">
              <button class="ctrl-card" @click="resetAllAlerts">
                <PhBellSlash class="ctrl-card-icon ctrl-yellow" weight="regular" aria-hidden="true" />
                <span class="ctrl-card-label">Dismiss Alerts</span>
              </button>
              <button class="ctrl-card" @click="switchTab('machines')">
                <PhFactory class="ctrl-card-icon ctrl-purple" weight="regular" aria-hidden="true" />
                <span class="ctrl-card-label">View Machine Report</span>
              </button>
              <button
                class="ctrl-card"
                :disabled="!collectionCandidates.length || requestingCollection"
                :title="collectionCandidates.length ? 'Request collection for bins at or above 90%' : 'No bins are at or above 90%'"
                @click="requestBinCollection"
              >
                <PhTrash class="ctrl-card-icon ctrl-gray" weight="regular" aria-hidden="true" />
                <span class="ctrl-card-label">
                  {{ requestingCollection
                    ? 'Sending Request...'
                    : collectionCandidates.length
                      ? `Request Bin Collection (${collectionCandidates.length})`
                      : 'No Bins Need Collection' }}
                </span>
              </button>
            </div>
          </div>

        </div>
      </div>

      <!-- ── TRANSACTIONS ── -->
      <div v-if="activeTab === 'transactions'" class="tab-content">
        <div class="section-card">
          <div class="tx-intro">
            <h3 class="card-title-bar"><span class="title-sq"></span> TRANSACTION LOG</h3>
            <p class="tx-help">
              One row for every item put into a machine.
              <strong class="tx-help-ok">Accepted</strong> items go into a bin and earn points.
              <strong class="tx-help-rej">Rejected</strong> items are given back to the user with 0 points, nothing is taken away.
            </p>
          </div>

          <!-- Totals for the current filters -->
          <div class="tx-summary" aria-live="polite">
            <div class="tx-sum-card">
              <span class="tx-sum-value">{{ txNum(txSummary.items) }}</span>
              <span class="tx-sum-label">Items</span>
            </div>
            <div class="tx-sum-card tx-sum-ok">
              <span class="tx-sum-value">{{ txNum(txSummary.accepted) }}</span>
              <span class="tx-sum-label">Accepted<template v-if="txSummary.items"> ({{ txPct(txSummary.accepted) }}%)</template></span>
            </div>
            <div class="tx-sum-card tx-sum-rej">
              <span class="tx-sum-value">{{ txNum(txSummary.rejected) }}</span>
              <span class="tx-sum-label">Rejected<template v-if="txSummary.items"> ({{ txPct(txSummary.rejected) }}%)</template></span>
            </div>
            <div class="tx-sum-card">
              <span class="tx-sum-value">{{ txNum(txSummary.points) }}</span>
              <span class="tx-sum-label">Points given</span>
            </div>
          </div>
          <p class="tx-sum-note">{{ txFiltersActive ? 'Totals for the filters below.' : 'Totals for all transactions.' }}</p>

          <!-- Filters, each with a visible label -->
          <div class="tx-filters">
            <div class="tx-field">
              <span>Show</span>
              <div class="tx-segment" role="group" aria-label="Show">
                <button v-for="opt in TX_RESULT_OPTIONS" :key="opt.value" type="button"
                  :class="['tx-seg-btn', { active: txFilter === opt.value }]" :aria-pressed="txFilter === opt.value"
                  @click="setTxResult(opt.value)">{{ opt.label }}</button>
              </div>
            </div>
            <label class="tx-field tx-field-search">
              <span>Search</span>
              <input v-model="txSearch" @input="debouncedTxSearch" @keyup.enter="searchTransactions"
                type="search" placeholder="Name, email, user ID or material" class="search-input" />
            </label>
            <label class="tx-field">
              <span>Machine</span>
              <select v-model="txMachine" @change="filterTransactions" class="filter-select">
                <option value="">All machines</option>
                <option v-for="m in adminMachines" :key="m.id" :value="m.id">{{ m.name }}</option>
              </select>
            </label>
            <label class="tx-field date-field picker-field" @click="openDatePicker">
              <span>From</span>
              <input v-model="txDateFrom" type="date" @change="filterTransactions" />
            </label>
            <label class="tx-field date-field picker-field" @click="openDatePicker">
              <span>To</span>
              <input v-model="txDateTo" type="date" @change="filterTransactions" />
            </label>
            <button v-if="txFiltersActive" class="action-btn tx-clear" @click="clearTransactionFilters">Clear filters</button>
            <button class="action-btn filter-action export-btn tx-export" :disabled="exportingTable === 'transactions'" @click="exportTable('transactions')"><PhDownloadSimple weight="regular" aria-hidden="true" /> {{ exportingTable === 'transactions' ? 'Exporting…' : 'Export Excel' }}</button>
          </div>

          <div v-if="loadingTransactions" class="loading-overlay"><div class="spinner-lg"></div></div>
          <div v-else class="table-wrap">
            <table class="data-table tx-table">
              <thead>
                <tr>
                  <th>When</th>
                  <th>User</th>
                  <th>Machine</th>
                  <th>Item</th>
                  <th>Result</th>
                  <th title="How sure the AI was about the material. Below 60% the item is rejected.">
                    AI confidence <span class="tx-th-hint" aria-hidden="true">?</span>
                  </th>
                  <th class="num">Points</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tx in transactions" :key="tx.id" :class="{ 'tx-row-rej': !tx.is_valid }">
                  <td>
                    <div class="tx-main">{{ txDate(tx.created_at) }}</div>
                    <div class="tx-sub">{{ txTime(tx.created_at) }}</div>
                  </td>
                  <td>
                    <div class="tx-main">{{ txUserName(tx.user) }}</div>
                    <div v-if="tx.user" class="tx-sub mono">{{ formatUserId(tx.user) }}</div>
                  </td>
                  <td>
                    <div class="tx-main">{{ tx.machine?.name || '—' }}</div>
                    <div v-if="tx.machine?.location_name" class="tx-sub">{{ tx.machine.location_name }}</div>
                  </td>
                  <td>
                    <span class="item-cell">
                      <svg v-if="materialIconSvg(tx.material_selected)" class="material-icon-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(tx.material_selected)"></svg>
                      {{ txItemLabel(tx) }}
                    </span>
                  </td>
                  <td>
                    <span :class="['tx-badge', tx.is_valid ? 'tx-badge-ok' : 'tx-badge-rej']">
                      <PhCheckCircle v-if="tx.is_valid" weight="fill" aria-hidden="true" />
                      <PhXCircle v-else weight="fill" aria-hidden="true" />
                      {{ tx.is_valid ? 'Accepted' : 'Rejected' }}
                    </span>
                    <div class="tx-sub tx-reason">{{ txReason(tx) }}</div>
                  </td>
                  <td>
                    <span v-if="tx.ai_confidence != null" :class="['tx-conf', txConfClass(tx.ai_confidence)]">
                      {{ Math.round(tx.ai_confidence * 100) }}%
                    </span>
                    <span v-else class="muted">—</span>
                  </td>
                  <td class="num">
                    <span v-if="tx.points_earned > 0" class="pts-green">+{{ tx.points_earned }}</span>
                    <span v-else-if="tx.points_deducted > 0" class="pts-red">-{{ tx.points_deducted }}</span>
                    <span v-else class="muted">0</span>
                  </td>
                </tr>
                <tr v-if="!transactions.length">
                  <td colspan="7" class="empty-cell">
                    No items match these filters.
                    <button v-if="txFiltersActive" class="tx-link-btn" @click="clearTransactionFilters">Clear filters</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <AdminPagination :current-page="txPage" :last-page="txLastPage"
            :per-page="txPerPage" :total="txTotal"
            @change-page="goToTxPage" @change-per-page="changeTxPerPage" />
        </div>
      </div>

      <!-- ── USERS ── -->
      <div v-if="activeTab === 'users'" class="tab-content">
        <div v-if="loadingUsers" class="loading-overlay"><div class="spinner-lg"></div></div>
        <div v-else class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> ALL USERS</h3>
            <div class="topbar-right">
              <input v-model="userSearch" @keyup.enter="searchUsers" placeholder="Search users... (press Enter)" class="search-input" />
              <button class="action-btn filter-action export-btn" :disabled="exportingTable === 'users'" @click="exportTable('users')"><PhDownloadSimple weight="regular" aria-hidden="true" /> {{ exportingTable === 'users' ? 'Exporting…' : 'Export Excel' }}</button>
            </div>
          </div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="sortable" @click="sortUsersBy('id')">ID{{ usersSortIndicator('id') }}</th>
                  <th class="sortable" @click="sortUsersBy('name')">Name{{ usersSortIndicator('name') }}</th>
                  <th class="sortable" @click="sortUsersBy('email')">Email{{ usersSortIndicator('email') }}</th>
                  <th class="sortable" @click="sortUsersBy('total_points')">Points{{ usersSortIndicator('total_points') }}</th>
                  <th class="sortable" @click="sortUsersBy('role')">Role{{ usersSortIndicator('role') }}</th>
                  <th class="sortable" @click="sortUsersBy('is_verified')">Verified{{ usersSortIndicator('is_verified') }}</th>
                  <th class="sortable" @click="sortUsersBy('created_at')">Created{{ usersSortIndicator('created_at') }}</th>
                  <th>Actions</th>
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
                  <td>
                    <span :class="['role-badge', 'role-' + user.role]">{{ user.role }}</span>
                    <span v-if="user.account_status === 'suspended'" class="suspended-badge">suspended</span>
                  </td>
                  <td>
                    <PhCheckCircle v-if="user.is_verified" class="verified-icon verified-yes" weight="regular" aria-hidden="true" />
                    <PhXCircle v-else class="verified-icon verified-no" weight="regular" aria-hidden="true" />
                  </td>
                  <td class="muted small">{{ formatDate(user.created_at) }}</td>
                  <td class="user-actions-cell">
                    <div class="user-actions">
                      <button class="action-btn edit-btn" @click="editUser(user)">
                        <PhPencilSimple class="user-action-icon" weight="regular" aria-hidden="true" />
                        Edit
                      </button>
                      <button v-if="user.id !== auth.user?.id"
                              :class="['action-btn', user.account_status === 'suspended' ? 'reactivate-btn' : 'suspend-btn']"
                              @click="toggleSuspend(user)">
                        {{ user.account_status === 'suspended' ? 'Reactivate' : 'Suspend' }}
                      </button>
                      <button class="action-btn del-btn" @click="deleteUser(user.id)">
                        <PhTrash class="user-action-icon" weight="regular" aria-hidden="true" />
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="!users.length">
                  <td colspan="8" class="empty-cell">No users found</td>
                </tr>
              </tbody>
            </table>
          </div>
          <AdminPagination :current-page="usersPage" :last-page="usersLastPage"
            :per-page="usersPerPage" :total="usersTotal"
            @change-page="goToUsersPage" @change-per-page="changeUsersPerPage" />
        </div>
      </div>

      <!-- ── MACHINES ── -->
      <div v-if="activeTab === 'machines'" class="tab-content">
        <!-- Reward Points Configuration -->
        <div class="section-card">
          <h3 class="card-title-bar"><span class="title-sq title-sq-yellow"></span> REWARD POINTS CONFIGURATION</h3>
          <div class="reward-materials">
            <div class="reward-mat-card" v-for="mat in rewardMaterials" :key="mat.key">
              <div class="reward-mat-label">
                <svg v-if="materialIconSvg(mat.key)" class="material-icon-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(mat.key)"></svg>
                {{ mat.label }}
              </div>
              <div class="reward-mat-control">
                <input class="reward-input" type="number" v-model.number="rewardEditValues[mat.key]" min="0" max="9999" />
                <button class="update-btn" @click="updateReward(mat.key)" :disabled="savingReward === mat.key">
                  {{ savingReward === mat.key ? '...' : 'Update' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- 2-bin compactor: max items per material per session (= one compactor batch) -->
        <div class="section-card">
          <h3 class="card-title-bar"><span class="title-sq title-sq-yellow"></span> COMPACTOR SETTINGS</h3>
          <p class="compactor-hint">
            How many items of each material one session may put in. The compactor presses a batch
            at this number, and the kiosk ends the session by itself once a material reaches it.
          </p>
          <div class="form-row">
            <div class="form-group">
              <label>Max plastic per session <span class="label-hint">1–10</span></label>
              <input v-model.number="compactorSettings.plastic" type="number" min="1" max="10" step="1" />
            </div>
            <div class="form-group">
              <label>Max aluminum per session <span class="label-hint">1–10</span></label>
              <input v-model.number="compactorSettings.aluminum" type="number" min="1" max="10" step="1" />
            </div>
          </div>
          <button class="add-btn" :disabled="savingCompactorSettings" @click="saveCompactorSettings">
            {{ savingCompactorSettings ? 'Saving...' : 'Save' }}
          </button>
        </div>

        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> RVM MACHINES</h3>
            <div class="topbar-right">
              <button class="action-btn filter-action export-btn" :disabled="exportingTable === 'machines'" @click="exportTable('machines')"><PhDownloadSimple weight="regular" aria-hidden="true" /> {{ exportingTable === 'machines' ? 'Exporting…' : 'Export Excel' }}</button>
              <button class="add-btn" @click="openAddMachine">+ Add Machine</button>
            </div>
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
              <p class="machine-loc"><PhMapPin weight="regular" aria-hidden="true" /> {{ machine.location_name || 'No location set' }}</p>
              <p class="machine-heartbeat">
                <span :class="['hb-dot', 'hb-' + machineConnectivity(machine, nowTick)]"></span>
                <template v-if="machineConnectivity(machine, nowTick) === 'never'">Never reported</template>
                <template v-else>{{ machineConnectivity(machine, nowTick) === 'online' ? 'Online' : 'Offline' }} · last seen {{ relativeTime(machine.last_seen_at) }}</template>
                <span v-if="machine.fault_active" class="fault-badge">FAULT{{ machine.fault_reason ? ': ' + machine.fault_reason : '' }}</span>
              </p>
              <div class="bin-admin-grid">
                <div v-for="bin in binTypes" :key="bin.id" class="bin-admin">
                  <span>
                    <svg v-if="materialIconSvg(bin.id)" class="material-icon-inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="materialIconSvg(bin.id)"></svg>
                    {{ bin.label }}
                  </span>
                  <div class="bin-bar-sm">
                    <div :class="['bin-fill-sm', getBinClass(machine[bin.id + '_level'])]"
                      :style="{ width: machine[bin.id + '_level'] + '%' }"></div>
                  </div>
                  <span :class="machine[bin.id + '_level'] >= 90 ? 'text-red' : 'text-muted-sm'">
                    {{ machine[bin.id + '_level'] }}%
                  </span>
                </div>
              </div>
              <!-- Everyday actions in an even 2×2 grid; Delete sits apart below
                   a divider so it can't be hit by accident next to Edit. -->
              <div class="machine-actions">
                <div class="machine-actions-grid">
                  <button class="action-btn machine-btn edit-btn" @click="openEditMachine(machine)">
                    <PhPencilSimple weight="regular" aria-hidden="true" /> Edit
                  </button>
                  <button class="action-btn machine-btn maint-btn" @click="openMaintenance(machine)">
                    <PhWrench weight="regular" aria-hidden="true" /> Maintenance
                  </button>
                  <button class="action-btn machine-btn" @click="resetBins(machine)">
                    <PhArrowCounterClockwise weight="regular" aria-hidden="true" /> Reset Bins
                  </button>
                  <button class="action-btn machine-btn" @click="historyMachine = machine">
                    <PhClockCounterClockwise weight="regular" aria-hidden="true" /> Service History
                  </button>
                </div>
                <button class="action-btn machine-btn machine-delete" @click="deleteMachine(machine.id)">
                  <PhTrash weight="regular" aria-hidden="true" /> Delete machine
                </button>
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
            <div class="topbar-right">
              <input v-model="sessionsSearch" @keyup.enter="searchSessions" placeholder="Search user / machine / status... (press Enter)" class="search-input" />
              <button class="action-btn filter-action export-btn" :disabled="exportingTable === 'sessions'" @click="exportTable('sessions')"><PhDownloadSimple weight="regular" aria-hidden="true" /> {{ exportingTable === 'sessions' ? 'Exporting…' : 'Export Excel' }}</button>
            </div>
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
          <AdminPagination :current-page="sessionsPage" :last-page="sessionsLastPage"
            :per-page="sessionsPerPage" :total="sessionsTotal"
            @change-page="goToSessionsPage" @change-per-page="changeSessionsPerPage" />
        </div>
      </div>

      <!-- ── AUDIT LOG ── -->
      <div v-if="activeTab === 'audit'" class="tab-content">
        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> AUDIT LOG</h3>
            <div class="topbar-right">
              <input v-model="auditSearch" @keyup.enter="searchAudit" placeholder="Search admin / action / details... (press Enter)" class="search-input" />
              <select v-model="auditAction" @change="searchAudit" class="filter-select">
                <option value="">All actions</option>
                <option v-for="a in auditActions" :key="a" :value="a">{{ auditActionLabel(a) }}</option>
              </select>
            </div>
          </div>
          <div class="filter-toolbar">
            <div class="date-range">
              <label class="date-field picker-field" @click="openDatePicker">
                <span>From</span>
                <input v-model="auditDateFrom" type="date" @change="searchAudit" />
              </label>
              <label class="date-field picker-field" @click="openDatePicker">
                <span>To</span>
                <input v-model="auditDateTo" type="date" @change="searchAudit" />
              </label>
            </div>
            <!-- After the filters, so the order reads "filter, then export" on a phone too. -->
            <div class="filter-actions">
              <button class="action-btn filter-action export-btn" :disabled="exportingTable === 'audit'" @click="exportTable('audit')"><PhDownloadSimple weight="regular" aria-hidden="true" /> {{ exportingTable === 'audit' ? 'Exporting…' : 'Export Excel' }}</button>
            </div>
          </div>
          <div v-if="loadingAudit" class="loading-overlay"><div class="spinner-lg"></div></div>
          <div v-else class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Time</th><th>Admin</th><th>Action</th><th>Target</th><th>Details</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in auditRows" :key="row.id">
                  <td class="muted small">{{ row.time }}</td>
                  <td>{{ row.admin }}</td>
                  <td>{{ row.actionLabel }}</td>
                  <td class="mono small">{{ row.target }}</td>
                  <td>{{ row.message || '—' }}</td>
                </tr>
                <tr v-if="!auditRows.length">
                  <td colspan="5" class="empty-cell">No admin activity found</td>
                </tr>
              </tbody>
            </table>
          </div>
          <AdminPagination :current-page="auditPage" :last-page="auditLastPage"
            :per-page="auditPerPage" :total="auditTotal"
            @change-page="goToAuditPage" @change-per-page="changeAuditPerPage" />
        </div>
      </div>

      <!-- ── DETECTION REVIEW ── -->
      <div v-if="activeTab === 'detection'" class="tab-content">
        <div class="section-card">
          <div class="card-header detection-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> {{ $t('admin.detectionReview.title') }}</h3>
          </div>
          <div class="review-view-tabs">
            <button :class="['review-view-btn', { active: detectionView === 'pending' }]" @click="setDetectionView('pending')">
              {{ $t('admin.detectionReview.pending') }}
            </button>
            <button :class="['review-view-btn', { active: detectionView === 'history' }]" @click="setDetectionView('history')">
              {{ $t('admin.detectionReview.history') }}
            </button>
            <button :class="['review-view-btn', { active: detectionView === 'test' }]" @click="setDetectionView('test')">
              {{ $t('admin.detectionReview.testData') }}
            </button>
          </div>
          <div v-if="detectionView === 'history'" class="history-filters">
            <button v-for="status in detectionHistoryFilters" :key="status.value"
              :class="['history-filter-btn', { active: detectionHistoryStatus === status.value }]"
              @click="setDetectionHistoryStatus(status.value)">
              {{ status.label }}
            </button>
          </div>
          <p v-if="detectionView === 'test'" class="test-data-note">
            {{ $t('admin.detectionReview.testDataInfo') }}
          </p>
          <div class="filter-toolbar detection-filter-toolbar">
            <label class="date-field detection-date-preset">
              <span>{{ $t('admin.detectionReview.capturedDate') }}</span>
              <select class="filter-select" :value="activeDetectionDateFilter.preset"
                @change="setDetectionDatePreset($event.target.value)">
                <option v-for="preset in detectionDatePresetOptions" :key="preset.value" :value="preset.value">
                  {{ preset.label }}
                </option>
              </select>
            </label>
            <div v-if="activeDetectionDateFilter.preset === 'custom'" class="date-range detection-custom-range">
              <label class="date-field picker-field" @click="openDatePicker">
                <span>{{ $t('admin.detectionReview.from') }}</span>
                <input type="date" :value="activeDetectionDateFilter.from"
                  @change="setDetectionCustomDate('from', $event.target.value)" />
              </label>
              <label class="date-field picker-field" @click="openDatePicker">
                <span>{{ $t('admin.detectionReview.to') }}</span>
                <input type="date" :value="activeDetectionDateFilter.to"
                  @change="setDetectionCustomDate('to', $event.target.value)" />
              </label>
            </div>
            <div v-if="detectionView === 'history'" class="filter-actions">
              <button class="action-btn filter-action" @click="exportDetectionLogsExcel">
                <PhDownloadSimple weight="regular" aria-hidden="true" /> {{ $t('admin.detectionReview.exportReviewedExcel') }}
              </button>
            </div>
          </div>
          <div v-if="loadingDetectionLogs" class="loading-overlay"><div class="spinner-lg"></div></div>
          <div v-else-if="!detectionLogs.length" class="empty-cell">
            {{ detectionView === 'pending'
              ? $t('admin.detectionReview.noPending')
              : detectionView === 'history'
                ? $t('admin.detectionReview.noHistory')
                : $t('admin.detectionReview.noTestData') }}
          </div>
          <TransitionGroup v-else name="detection-card" tag="div" class="detection-grid">
            <div v-for="log in detectionLogs" :key="log.id" class="detection-card"
              v-swipe="{ enabled: detectionView === 'pending', onRight: () => swipeCorrect(log), onLeft: () => swipeIncorrect(log) }">
              <template v-if="detectionView === 'pending'">
                <div class="swipe-hint swipe-hint-correct" aria-hidden="true">
                  <PhCheck weight="bold" /> {{ $t('admin.detectionReview.correct') }}
                </div>
                <div class="swipe-hint swipe-hint-incorrect" aria-hidden="true">
                  <PhX weight="bold" /> {{ $t('admin.detectionReview.incorrect') }}
                </div>
              </template>
              <!-- Tap the photo for the full-size viewer. v-tap ignores a swipe (the finger moves). -->
              <img v-if="thumbnails[log.id]" :src="thumbnails[log.id]" class="detection-thumb tappable"
                :alt="$t('admin.detectionReview.captureAlt')" v-tap="() => openDetectionViewer(log)" />
              <PhCamera v-else class="detection-thumb placeholder" weight="regular" aria-hidden="true" />
              <div class="detection-meta">
                <div class="detection-badges">
                  <span v-if="log.is_mock" class="badge badge-warning">{{ $t('admin.detectionReview.mock') }}</span>
                  <span class="badge">{{ log.is_guest ? $t('admin.detectionReview.guest') : $t('admin.detectionReview.login') }}</span>
                  <span v-if="detectionView === 'history'"
                    :class="['badge', log.ground_truth_correct ? 'badge-reviewed-correct' : 'badge-reviewed-incorrect']">
                    {{ log.ground_truth_correct ? $t('admin.detectionReview.correct') : $t('admin.detectionReview.incorrect') }}
                  </span>
                </div>
                <p class="detection-result">{{ log.ai_detected_type || 'unknown' }} — {{ Math.round((log.ai_confidence || 0) * 100) }}%</p>
                <p class="detection-time">{{ formatDate(log.created_at) }}</p>
                <div v-if="detectionView === 'history'" class="review-history-meta">
                  <p><span>{{ $t('admin.detectionReview.actualMaterial') }}:</span> {{ materialLabel(log.ground_truth_label) }}</p>
                  <p><span>{{ $t('admin.detectionReview.reviewedBy') }}:</span> {{ log.reviewer?.name || '—' }}</p>
                  <p><span>{{ $t('admin.detectionReview.reviewedAt') }}:</span> {{ formatDate(log.reviewed_at) }}</p>
                </div>
              </div>
              <div v-if="detectionView === 'pending'" class="detection-actions">
                <button class="review-btn" :title="correctDisabledReason(log)"
                  :disabled="!isReviewable(log) || isUnknownPrediction(log)" @click="markCorrect(log)">
                  <PhCheck weight="regular" aria-hidden="true" /> {{ $t('admin.detectionReview.correct') }}
                </button>
                <button class="review-btn reject" :title="reviewDisabledReason(log)"
                  :disabled="!isReviewable(log)" @click="openIncorrectReview(log)">
                  <PhX weight="regular" aria-hidden="true" /> {{ $t('admin.detectionReview.incorrect') }}
                </button>
              </div>
              <p v-if="detectionView === 'pending' && !isReviewable(log)" class="detection-disabled-note">{{ reviewDisabledReason(log) }}</p>
              <p v-else-if="detectionView === 'pending' && isUnknownPrediction(log)" class="detection-disabled-note">
                {{ $t('admin.detectionReview.unknownMustAssign') }}
              </p>
            </div>
          </TransitionGroup>
          <AdminPagination :current-page="detectionPage" :last-page="detectionLastPage"
            :per-page="detectionPerPage" :total="detectionTotal"
            @change-page="goToDetectionPage" @change-per-page="changeDetectionPerPage" />
        </div>
      </div>

      <!-- ── REWARDS ── -->
      <div v-if="activeTab === 'rewards'" class="tab-content">
        <div class="section-card">
          <h3 class="card-title-bar"><span class="title-sq title-sq-yellow"></span> CASH REDEEM SETTINGS</h3>
          <div class="form-group"><label>Points per Unit</label><input v-model.number="cashRedeemSettings.points_per_unit" type="number" min="1" /></div>
          <div class="form-group"><label>RM per Unit</label><input v-model.number="cashRedeemSettings.rm_per_unit" type="number" min="0.01" step="0.01" /></div>
          <div class="form-group"><label>Minimum Points</label><input v-model.number="cashRedeemSettings.min_points" type="number" min="1" /></div>
          <button class="add-btn" :disabled="savingCashRedeemSettings" @click="saveCashRedeemSettings">
            {{ savingCashRedeemSettings ? 'Saving...' : 'Save' }}
          </button>
        </div>

        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> REWARD CATALOG</h3>
            <div class="topbar-right">
              <button class="action-btn filter-action export-btn" :disabled="exportingTable === 'rewardItems'" @click="exportTable('rewardItems')"><PhDownloadSimple weight="regular" aria-hidden="true" /> {{ exportingTable === 'rewardItems' ? 'Exporting…' : 'Export Excel' }}</button>
              <button class="add-btn" @click="openAddRewardItem">+ Add Reward</button>
            </div>
          </div>
          <div v-if="loadingRewardItems" class="loading-overlay"><div class="spinner-lg"></div></div>
          <div v-else class="reward-admin-grid">
            <div v-for="item in rewardItems" :key="item.id" class="reward-admin-card">
              <img v-if="item.image_url" :src="item.image_url" alt="" class="reward-admin-image" />
              <div class="reward-admin-header">
                <div>
                  <strong>{{ item.name }}</strong>
                  <span v-if="item.category" class="reward-admin-badge">{{ item.category }}</span>
                </div>
                <span :class="['status-badge', item.is_active ? 'status-active' : 'status-inactive']">
                  {{ item.is_active ? 'active' : 'inactive' }}
                </span>
              </div>
              <p class="reward-admin-sub" v-if="item.description">{{ item.description }}</p>
              <p class="reward-admin-sub"><PhStar weight="regular" aria-hidden="true" /> {{ item.points_cost }} pts · {{ item.stock === null ? 'Unlimited stock' : `${item.stock} in stock` }}</p>
              <div class="reward-admin-actions">
                <button class="action-btn edit-btn" @click="openEditRewardItem(item)">Edit</button>
                <button class="action-btn del-btn" @click="deleteRewardItem(item.id)">Delete</button>
              </div>
            </div>
            <div v-if="!rewardItems.length" class="empty-rewards">
              <p>No reward items yet. Add your first one.</p>
              <button class="add-btn" @click="openAddRewardItem">+ Add Reward</button>
            </div>
          </div>
          <AdminPagination :current-page="rewardItemsPage" :last-page="rewardItemsLastPage"
            :per-page="rewardItemsPerPage" :total="rewardItemsTotal"
            @change-page="goToRewardItemsPage" @change-per-page="changeRewardItemsPerPage" />
        </div>

        <div class="section-card">
          <div class="card-header">
            <h3 class="card-title-bar"><span class="title-sq"></span> REDEMPTION HISTORY</h3>
            <button class="action-btn filter-action export-btn" :disabled="exportingTable === 'redemptions'" @click="exportTable('redemptions')"><PhDownloadSimple weight="regular" aria-hidden="true" /> {{ exportingTable === 'redemptions' ? 'Exporting…' : 'Export Excel' }}</button>
          </div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr><th>User</th><th>Reward</th><th>Points Spent</th><th>E-Wallet</th><th>Date</th></tr>
              </thead>
              <tbody>
                <tr v-for="r in adminRedemptions" :key="r.id">
                  <td>{{ r.user?.name || '—' }}</td>
                  <td>{{ r.reward_name }}</td>
                  <td class="pts-red">-{{ r.points_spent }}</td>
                  <td class="muted small">{{ r.ewallet_provider ? `${r.ewallet_provider} · ${r.ewallet_account}` : '—' }}</td>
                  <td class="muted small">{{ formatDate(r.created_at) }}</td>
                </tr>
                <tr v-if="!adminRedemptions.length">
                  <td colspan="5" class="empty-cell">No redemptions yet</td>
                </tr>
              </tbody>
            </table>
          </div>
          <AdminPagination :current-page="redemptionsPage" :last-page="redemptionsLastPage"
            :per-page="redemptionsPerPage" :total="redemptionsTotal"
            @change-page="goToRedemptionsPage" @change-per-page="changeRedemptionsPerPage" />
        </div>
      </div>

      </div>
    </main>

    <!-- ── Edit User Modal ── -->
    <div v-if="editingUser" class="modal-overlay" @click.self="editingUser = null">
      <div class="modal">
        <button class="modal-close-btn" aria-label="Close modal" @click="editingUser = null">
          <PhX weight="bold" aria-hidden="true" />
        </button>
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
          <button class="action-btn edit-btn" @click="saveUser">Save</button>
        </div>
      </div>
    </div>

    <!-- ── Incorrect Detection Review Modal ── -->
    <div v-if="reviewingDetection" class="modal-overlay" @click.self="closeIncorrectReview">
      <div class="modal detection-review-modal">
        <!-- v-tap, not @click: this modal usually opens from a swipe, and a tap
             right after a swipe gets no click event (see directives/tap.js). -->
        <button class="modal-close-btn" :aria-label="$t('admin.detectionReview.closeModal')"
          v-tap="closeIncorrectReview">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>{{ $t('admin.detectionReview.incorrectTitle') }}</h3>
        <!-- Full photo, then the AI prediction right under it -->
        <div class="review-modal-summary">
          <img v-if="thumbnails[reviewingDetection.id]" :src="thumbnails[reviewingDetection.id]"
            class="review-modal-image" :alt="$t('admin.detectionReview.captureAlt')" />
          <PhCamera v-else class="review-modal-image placeholder" weight="regular" aria-hidden="true" />
          <div class="review-modal-prediction">
            <span>{{ $t('admin.detectionReview.aiPrediction') }}</span>
            <strong>{{ reviewingDetection.ai_detected_type || 'unknown' }} — {{ Math.round((reviewingDetection.ai_confidence || 0) * 100) }}%</strong>
          </div>
        </div>
        <p class="actual-material-prompt">{{ $t('admin.detectionReview.selectActual') }}</p>
        <!-- One tap saves (and closes the modal); a mistake is undone from the Undo toast. -->
        <div class="actual-material-grid">
          <button v-for="material in detectionMaterials" :key="material.value"
            class="actual-material-btn"
            :disabled="normalizedPrediction(reviewingDetection) === material.value"
            v-tap="() => saveIncorrectReview(material.value)">
            {{ material.label }}
            <small v-if="normalizedPrediction(reviewingDetection) === material.value">{{ $t('admin.detectionReview.aiPrediction') }}</small>
          </button>
        </div>
      </div>
    </div>

    <!-- ── Detection Photo Viewer (tap a card's photo) ── -->
    <div v-if="viewingDetection" class="modal-overlay detection-viewer-overlay" @click.self="closeDetectionViewer">
      <div class="detection-viewer">
        <button class="modal-close-btn" :aria-label="$t('admin.detectionReview.closeModal')"
          v-tap="closeDetectionViewer">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <img v-if="thumbnails[viewingDetection.id]" :src="thumbnails[viewingDetection.id]"
          class="detection-viewer-image" :alt="$t('admin.detectionReview.captureAlt')" />
        <div class="detection-viewer-caption">
          <span>{{ $t('admin.detectionReview.aiPrediction') }}</span>
          <strong>{{ viewingDetection.ai_detected_type || 'unknown' }} — {{ Math.round((viewingDetection.ai_confidence || 0) * 100) }}%</strong>
          <small>{{ formatDate(viewingDetection.created_at) }}</small>
        </div>
        <div v-if="detectionView === 'pending'" class="detection-actions detection-viewer-actions">
          <button class="review-btn" :title="correctDisabledReason(viewingDetection)"
            :disabled="!isReviewable(viewingDetection) || isUnknownPrediction(viewingDetection)"
            v-tap="viewerMarkCorrect">
            <PhCheck weight="regular" aria-hidden="true" /> {{ $t('admin.detectionReview.correct') }}
          </button>
          <button class="review-btn reject" :title="reviewDisabledReason(viewingDetection)"
            :disabled="!isReviewable(viewingDetection)" v-tap="viewerMarkIncorrect">
            <PhX weight="regular" aria-hidden="true" /> {{ $t('admin.detectionReview.incorrect') }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Add Machine Modal ── -->
    <MaintenanceHistoryModal v-if="historyMachine" :machine="historyMachine" @close="historyMachine = null" />

    <div v-if="showAddMachine" class="modal-overlay" @click.self="showAddMachine = false">
      <div class="modal">
        <button class="modal-close-btn" aria-label="Close modal" @click="showAddMachine = false">
          <PhX weight="bold" aria-hidden="true" />
        </button>
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
          <button class="action-btn edit-btn" @click="addMachine" :disabled="savingMachine">
            {{ savingMachine ? 'Adding...' : 'Add Machine' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Add Reward Modal ── -->
    <div v-if="showAddRewardItem" class="modal-overlay" @click.self="showAddRewardItem = false">
      <div class="modal reward-modal">
        <button class="modal-close-btn" aria-label="Close modal" @click="showAddRewardItem = false">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>Add Reward</h3>
        <div class="form-group"><label>Name *</label><input v-model="newRewardItem.name" type="text" /></div>
        <div class="form-group"><label>Description</label><input v-model="newRewardItem.description" type="text" /></div>
        <div class="form-group"><label>Category</label><input v-model="newRewardItem.category" type="text" placeholder="e.g. Food Voucher" /></div>
        <div class="form-row">
          <div class="form-group"><label>Points Cost *</label><input v-model.number="newRewardItem.points_cost" type="number" min="1" /></div>
          <div class="form-group"><label>Stock <span class="label-hint">Optional</span></label><input v-model="newRewardItem.stock" type="number" min="0" placeholder="Unlimited" /></div>
        </div>
        <div class="form-row">
          <div class="form-group picker-field" @click="openDatePicker"><label>Available From</label><input v-model="newRewardItem.valid_from" type="datetime-local" /></div>
          <div class="form-group picker-field" @click="openDatePicker"><label>Available Until</label><input v-model="newRewardItem.valid_until" type="datetime-local" /></div>
        </div>
        <div class="form-group"><label>Active</label><select v-model="newRewardItem.is_active"><option :value="true">Yes</option><option :value="false">No</option></select></div>
        <div class="form-group">
          <label>Image</label>
          <!-- Reward images are cropped to 4:3 so every card in the Rewards menu looks the same. -->
          <div v-if="rewardImagePreviewUrl" class="reward-image-preview">
            <img :src="rewardImagePreviewUrl" alt="" />
            <button type="button" class="action-btn edit-btn" @click="adjustRewardImage">
              <PhCrop weight="regular" aria-hidden="true" />
              {{ rewardItemImageFile ? 'Adjust crop' : 'Adjust current image' }}
            </button>
          </div>
          <label
            :class="['image-dropzone', { dragging: rewardImageDragging }]"
            @dragenter.prevent="rewardImageDragging = true"
            @dragover.prevent="rewardImageDragging = true"
            @dragleave.prevent="rewardImageDragging = false"
            @drop.prevent="handleRewardImageDrop"
          >
            <PhUploadSimple class="upload-icon" weight="regular" aria-hidden="true" />
            <strong>{{ rewardItemImageFile?.name || 'Drop image here or click to browse' }}</strong>
            <span>{{ rewardItemImageFile ? 'Click or drop another image to replace it' : 'PNG or JPG · cropped to 4:3 next' }}</span>
            <input class="file-input-hidden" type="file" accept="image/jpeg,image/png" @change="handleRewardImageChange" />
          </label>
        </div>
        <p v-if="rewardItemError" class="msg msg-err">{{ rewardItemError }}</p>
        <div class="modal-actions">
          <button class="action-btn edit-btn" :disabled="savingRewardItem" @click="addRewardItem">{{ savingRewardItem ? '...' : 'Add' }}</button>
        </div>
      </div>
    </div>

    <!-- ── Edit Reward Modal ── -->
    <div v-if="editingRewardItem" class="modal-overlay" @click.self="editingRewardItem = null">
      <div class="modal reward-modal">
        <button class="modal-close-btn" aria-label="Close modal" @click="editingRewardItem = null">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>Edit Reward</h3>
        <div class="form-group"><label>Name *</label><input v-model="editingRewardItem.name" type="text" /></div>
        <div class="form-group"><label>Description</label><input v-model="editingRewardItem.description" type="text" /></div>
        <div class="form-group"><label>Category</label><input v-model="editingRewardItem.category" type="text" /></div>
        <div class="form-row">
          <div class="form-group"><label>Points Cost *</label><input v-model.number="editingRewardItem.points_cost" type="number" min="1" /></div>
          <div class="form-group"><label>Stock <span class="label-hint">Optional</span></label><input v-model="editingRewardItem.stock" type="number" min="0" placeholder="Unlimited" /></div>
        </div>
        <div class="form-row">
          <div class="form-group picker-field" @click="openDatePicker"><label>Available From</label><input v-model="editingRewardItem.valid_from" type="datetime-local" /></div>
          <div class="form-group picker-field" @click="openDatePicker"><label>Available Until</label><input v-model="editingRewardItem.valid_until" type="datetime-local" /></div>
        </div>
        <div class="form-group"><label>Active</label><select v-model="editingRewardItem.is_active"><option :value="true">Yes</option><option :value="false">No</option></select></div>
        <div class="form-group">
          <label>Replace Image</label>
          <!-- Reward images are cropped to 4:3 so every card in the Rewards menu looks the same. -->
          <div v-if="rewardImagePreviewUrl" class="reward-image-preview">
            <img :src="rewardImagePreviewUrl" alt="" />
            <button type="button" class="action-btn edit-btn" @click="adjustRewardImage">
              <PhCrop weight="regular" aria-hidden="true" />
              {{ rewardItemImageFile ? 'Adjust crop' : 'Adjust current image' }}
            </button>
          </div>
          <label
            :class="['image-dropzone', { dragging: rewardImageDragging }]"
            @dragenter.prevent="rewardImageDragging = true"
            @dragover.prevent="rewardImageDragging = true"
            @dragleave.prevent="rewardImageDragging = false"
            @drop.prevent="handleRewardImageDrop"
          >
            <PhUploadSimple class="upload-icon" weight="regular" aria-hidden="true" />
            <strong>{{ rewardItemImageFile?.name || 'Drop replacement image here or click to browse' }}</strong>
            <span>{{ rewardItemImageFile ? 'Click or drop another image to replace it' : 'PNG or JPG · cropped to 4:3 next · leave empty to keep current image' }}</span>
            <input class="file-input-hidden" type="file" accept="image/jpeg,image/png" @change="handleRewardImageChange" />
          </label>
        </div>
        <p v-if="rewardItemError" class="msg msg-err">{{ rewardItemError }}</p>
        <div class="modal-actions">
          <button class="action-btn edit-btn" :disabled="savingRewardItem" @click="saveRewardItem">{{ savingRewardItem ? '...' : 'Save' }}</button>
        </div>
      </div>
    </div>

    <!-- ── Reward Image Crop Modal (opens over Add/Edit Reward) ── -->
    <div v-if="rewardCropSource" class="modal-overlay" @click.self="closeRewardCrop">
      <div class="modal reward-crop-modal">
        <button class="modal-close-btn" :aria-label="$t('admin.imageCropper.cancel')" @click="closeRewardCrop">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>{{ $t('admin.imageCropper.title') }}</h3>
        <ImageCropper ref="rewardCropper" :source="rewardCropSource" />
        <div class="modal-actions">
          <button class="action-btn" @click="closeRewardCrop">{{ $t('admin.imageCropper.cancel') }}</button>
          <button class="action-btn edit-btn" :disabled="!rewardCropper?.ready || applyingRewardCrop" @click="applyRewardCrop">
            {{ applyingRewardCrop ? '...' : $t('admin.imageCropper.apply') }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Edit Machine Modal ── -->
    <div v-if="editingMachine" class="modal-overlay" @click.self="editingMachine = null">
      <div class="modal">
        <button class="modal-close-btn" aria-label="Close modal" @click="editingMachine = null">
          <PhX weight="bold" aria-hidden="true" />
        </button>
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
          <button class="action-btn edit-btn" @click="saveMachine" :disabled="savingMachine">
            {{ savingMachine ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Kiosk Maintenance Modal ── -->
    <div v-if="maintainingMachine" class="modal-overlay" @click.self="closeMaintenance">
      <div class="modal">
        <button class="modal-close-btn" aria-label="Close modal" @click="closeMaintenance">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>Kiosk Maintenance — {{ maintainingMachine.name }}</h3>
        <p class="maint-hint">
          Scan the QR code currently shown on this kiosk's own screen to confirm
          you're physically at the machine. Its Chromium will close immediately
          and stay closed until the machine is rebooted or logged in again.
        </p>

        <div class="qr-frame" :class="{ 'camera-active': maintCameraActive }">
          <video v-show="maintCameraActive" ref="maintVideoRef" class="camera-feed" autoplay playsinline muted></video>
          <canvas ref="maintCanvasRef" class="hidden-canvas"></canvas>
          <div v-if="maintCameraActive" class="scan-line"></div>
          <div v-if="!maintCameraActive" class="qr-placeholder">
            <PhCamera weight="regular" aria-hidden="true" />
          </div>
        </div>

        <button class="camera-btn" @click="toggleMaintCamera" :disabled="maintLoading">
          {{ maintCameraActive ? 'Close Camera' : 'Open Camera' }}
        </button>

        <div class="form-group" style="margin-top: 14px">
          <label>Or paste the QR token manually</label>
          <input
            v-model="maintTokenInput"
            type="text"
            placeholder="e.g. AB12CD"
            @keyup.enter="submitMaintenance"
          />
        </div>

        <p v-if="maintError" class="form-error">{{ maintError }}</p>

        <div class="modal-actions">
          <button class="action-btn del-btn" @click="submitMaintenance" :disabled="maintLoading || !maintTokenInput.trim()">
            {{ maintLoading ? 'Closing...' : 'Confirm & Close Kiosk' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Email Report Modal ── -->
    <div v-if="showEmailReportModal" class="modal-overlay" @click.self="showEmailReportModal = false">
      <div class="modal">
        <button class="modal-close-btn" aria-label="Close modal" :disabled="sendingReportEmail" @click="showEmailReportModal = false">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <h3>Send Report via Email</h3>
        <div class="form-group">
          <label>Recipient Email *</label>
          <input v-model="emailReportAddress" type="email" placeholder="e.g. swcorp@example.com" />
        </div>
        <p v-if="emailReportError" class="form-error">{{ emailReportError }}</p>
        <div class="modal-actions">
          <button class="action-btn edit-btn" :disabled="sendingReportEmail" @click="sendReportEmail">
            {{ sendingReportEmail ? 'Sending...' : 'Send' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── Confirm modal (replaces native confirm()) ── -->
    <div v-if="confirmState.show" class="modal-overlay" @click.self="settleConfirm(false)">
      <div class="modal confirm-modal">
        <button class="modal-close-btn" aria-label="Close modal" @click="settleConfirm(false)">
          <PhX weight="bold" aria-hidden="true" />
        </button>
        <p class="confirm-message">{{ confirmState.message }}</p>
        <div class="modal-actions">
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
        <PhWarning v-if="toastState.type === 'error'" class="toast-icon" weight="regular" aria-hidden="true" />
        <PhCheckCircle v-else class="toast-icon" weight="regular" aria-hidden="true" />
        {{ toastState.message }}
      </div>
    </Transition>

    <!-- Keyed per review: a new review while this is up makes the old toast
         slide down and out before the new one slides up (out-in). -->
    <Transition name="undo-swap" mode="out-in">
      <div v-if="detectionUndo.show" :key="detectionUndo.key" class="toast detection-undo" role="status">
        <PhCheckCircle class="toast-icon" weight="regular" aria-hidden="true" />
        {{ detectionUndo.message }}
        <button class="undo-review-btn" :disabled="detectionUndo.loading" @click="undoDetectionReview">
          {{ detectionUndo.loading ? $t('admin.detectionReview.undoing') : $t('admin.detectionReview.undo') }}
        </button>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive, computed, inject, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/store/auth'
import api from '@/services/api'
import jsQR from 'jsqr'
import { extractQrToken } from '@/utils/qrToken'
import { materialIconSvg } from '@/utils/materialIcons'
import AdminPagination from '@/components/admin/AdminPagination.vue'
import ImageCropper from '@/components/admin/ImageCropper.vue'
import MaintenanceHistoryModal from '@/components/admin/MaintenanceHistoryModal.vue'
import {
  PhRecycle, PhSun, PhMoon, PhUser, PhList, PhArrowsClockwise, PhWarning, PhX,
  PhCaretLeft, PhCaretRight,
  PhChartBar, PhReceipt, PhUsers, PhFactory, PhClipboardText, PhListChecks, PhMagnifyingGlass, PhGift,
  PhGlobe, PhStar, PhPackage, PhRobot, PhWarningOctagon, PhGear, PhWifiSlash,
  PhDownloadSimple, PhEnvelopeSimple, PhBellSlash, PhTrash,
  PhCheckCircle, PhXCircle, PhCamera, PhCheck, PhMapPin, PhPencilSimple, PhUploadSimple, PhCrop,
  PhWrench, PhArrowCounterClockwise, PhClockCounterClockwise,
} from '@phosphor-icons/vue'
import { resolveLoadingFlag } from '@/utils/admin/tabLoading.js'
import { formatAuditLog } from '@/utils/admin/auditLog.js'
import { buildRewardUpdatePayload } from '@/utils/admin/rewardConfig.js'
import { validateMachineName } from '@/utils/admin/validateMachine.js'
import { normalizeMachine } from '@/utils/admin/normalizeMachine.js'
import { machineConnectivity, buildMachineAlerts, connectivitySummary } from '@/utils/admin/machineAlerts.js'
import { isFresh } from '@/utils/admin/tabFreshness.js'
import { toDatetimeLocalValue } from '@/utils/admin/toDatetimeLocalValue.js'
import { DETECTION_DATE_PRESETS, detectionDateRange } from '@/utils/admin/detectionDateRange.js'
import { buildAdminQuery, parseAdminQuery } from '@/utils/admin/adminTabState.js'
import { itemLabel, resultReason, confidenceLevel, percentOf } from '@/utils/admin/transactionDisplay.js'
import { downloadExcel } from '@/utils/admin/excelExport.js'
import { filterHidden, pruneSettled, removeLog, restoreLog } from '@/utils/admin/detectionOptimistic.js'
import {
  Chart as ChartJS, CategoryScale, LinearScale, BarElement,
  ArcElement, Tooltip, Legend
} from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'
ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Tooltip, Legend)

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { t } = useI18n()
const theme = inject('theme')
const toggleTheme = inject('toggleTheme')

// ── State ──
const activeTab = ref('dashboard')
const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)

// Neither ref is tied to actual viewport width on its own, but each only
// makes sense on one side of the 768px breakpoint:
// - mobileSidebarOpen left true past 768px makes `.sidebar-footer`'s
//   `v-if="!sidebarCollapsed || mobileSidebarOpen"` render full content
//   into a `.sidebar.collapsed` 60px rail — it overflows/wraps instead of
//   collapsing.
// - sidebarCollapsed left true at ≤768px width hides the "RVM Admin"
//   header title (its own plain `v-if="!sidebarCollapsed"`) with the
//   collapse button now hidden on mobile (collapse-to-icon-rail is a
//   desktop-only affordance) — nothing on mobile could ever un-hide it.
// Reset each one on resize past its own boundary so neither can be
// inherited from the other layout.
function handleResize() {
  if (window.innerWidth > 768 && mobileSidebarOpen.value) {
    mobileSidebarOpen.value = false
  }
  if (window.innerWidth <= 768 && sidebarCollapsed.value) {
    sidebarCollapsed.value = false
  }
}
const lastUpdated = ref('—')
const isLive = ref(true)

const loadingStats = ref(false)
const loadingUsers = ref(false)
const loadingMachines = ref(false)
const loadingSessions = ref(false)
const loadingAudit = ref(false)
const loadingTransactions = ref(false)

const tabError = ref('')   // surfaced error message per tab

// Skip re-fetching a tab if it was loaded this recently — makes rapid tab
// switching feel instant instead of flashing a spinner every click.
const TAB_STALE_MS = 10000
const tabFetchedAt = reactive({ dashboard: 0, transactions: 0, users: 0, machines: 0, sessions: 0, audit: 0, detection: 0 })

// Detection Review's Pending/History/Test Data sub-views (plus the History
// All/Correct/Incorrect filter, date range and page) each return different
// rows from the same 'detection' tab slot — the single tabFetchedAt.detection
// timestamp above can't tell those apart, so switching sub-tabs always
// re-hit the database. This caches each exact filter combination separately
// so revisiting one within TAB_STALE_MS is instant, same as the main tabs.
const detectionCache = reactive({})
function detectionCacheKey() {
  const f = activeDetectionDateFilter.value
  return [detectionView.value, detectionHistoryStatus.value, detectionPage.value, detectionPerPage.value, f.from, f.to].join('|')
}
function loadDetectionData(showSpinner = true) {
  const key = detectionCacheKey()
  const cached = detectionCache[key]
  if (isFresh(cached?.fetchedAt, Date.now(), TAB_STALE_MS)) {
    detectionLogs.value     = cached.logs
    detectionTotal.value    = cached.total
    detectionLastPage.value = cached.lastPage
    releaseThumbnails(cached.logs)
    cached.logs.forEach(loadThumbnail)
    return
  }
  fetchTabData('detection', showSpinner)
}

const userSearch = ref('')
const txSearch = ref('')
const txFilter = ref('')
const txMachine = ref('')
const txDateFrom = ref('')
const txDateTo = ref('')
const txSummary = ref({ items: 0, accepted: 0, rejected: 0, points: 0 })
const TX_RESULT_OPTIONS = [
  { value: '', label: 'All' },
  { value: 'valid', label: 'Accepted' },
  { value: 'rejected', label: 'Rejected' },
]
const txFiltersActive = computed(() =>
  !!(txSearch.value || txFilter.value || txMachine.value || txDateFrom.value || txDateTo.value))
const sessionsSearch = ref('')

const usersPage = ref(1)
const usersPerPage = ref(20)
const usersTotal = ref(0)
const usersLastPage = ref(1)
const usersSortColumn = ref('id')
const usersSortDirection = ref('desc')

const txPage = ref(1)
const txPerPage = ref(20)
const txTotal = ref(0)
const txLastPage = ref(1)

const sessionsPage = ref(1)
const sessionsPerPage = ref(20)
const sessionsTotal = ref(0)
const sessionsLastPage = ref(1)

const auditLogs = ref([])
const auditActions = ref([])
const auditSearch = ref('')
const auditAction = ref('')
const auditDateFrom = ref('')
const auditDateTo = ref('')
const auditPage = ref(1)
const auditPerPage = ref(20)
const auditTotal = ref(0)
const auditLastPage = ref(1)
const auditRows = computed(() => auditLogs.value.map(formatAuditLog))
function auditActionLabel(a) {
  return formatAuditLog({ action: a }).actionLabel
}

const loadingDetectionLogs = ref(false)
const loadingRewardItems = ref(false)
const detectionLogs      = ref([])
const detectionPage      = ref(1)
const detectionPerPage   = ref(20)
const detectionTotal     = ref(0)
const detectionLastPage  = ref(1)
const thumbnails         = ref({})
const detectionView      = ref('pending')
const detectionHistoryStatus = ref('reviewed')
const reviewingDetection = ref(null)
const viewingDetection   = ref(null) // card whose photo is open full-size
// Reviewed cards taken out of the list before the server confirmed the save
// (id -> savedAt, null while in flight). See utils/admin/detectionOptimistic.js.
let hiddenDetectionIds = new Map()
let detectionRequestSeq = 0 // latest Detection list request; older answers are dropped
const detectionUndo = ref({ show: false, id: null, message: '', loading: false, key: 0 })
let detectionUndoTimer = null
// In-flight review saves (id -> Promise<boolean>), so Undo pressed before the
// server confirmed can wait for the save instead of racing it.
const detectionSaves = new Map()

const recentDetectionRange = detectionDateRange('last_7_days')
const detectionDateFilters = reactive({
  pending: { preset: 'all', from: '', to: '' },
  history: { preset: 'last_7_days', ...recentDetectionRange },
  test: { preset: 'last_7_days', ...recentDetectionRange },
})
const activeDetectionDateFilter = computed(() => detectionDateFilters[detectionView.value])
const detectionReviewStatus = computed(() => {
  if (detectionView.value === 'pending') return 'pending'
  if (detectionView.value === 'test') return 'test'
  return detectionHistoryStatus.value
})
const detectionHistoryFilters = computed(() => [
  { value: 'reviewed', label: t('admin.detectionReview.all') },
  { value: 'correct', label: t('admin.detectionReview.correct') },
  { value: 'incorrect', label: t('admin.detectionReview.incorrect') },
])
const detectionDatePresetOptions = computed(() => DETECTION_DATE_PRESETS.map(value => ({
  value,
  label: t(`admin.detectionReview.datePresets.${value}`),
})))
const detectionMaterials = computed(() => [
  { value: 'aluminum', label: t('admin.detectionReview.materials.aluminum') },
  { value: 'plastic',  label: t('admin.detectionReview.materials.plastic') },
  { value: 'glass',    label: t('admin.detectionReview.materials.glass') },
  { value: 'paper',    label: t('admin.detectionReview.materials.paper') },
  { value: 'wood',     label: t('admin.detectionReview.materials.wood') },
  { value: 'metal',    label: t('admin.detectionReview.materials.metal') },
  { value: 'brick',    label: t('admin.detectionReview.materials.brick') },
  { value: 'other',    label: t('admin.detectionReview.materials.other') },
])

const cashRedeemSettings = ref({ points_per_unit: 100, rm_per_unit: 0.10, min_points: 500 })
const savingCashRedeemSettings = ref(false)
const compactorSettings = ref({ plastic: 3, aluminum: 3 })
const savingCompactorSettings = ref(false)
const rewardItems = ref([])
const rewardItemsPage = ref(1)
const rewardItemsPerPage = ref(20)
const rewardItemsTotal = ref(0)
const rewardItemsLastPage = ref(1)
const showAddRewardItem = ref(false)
const editingRewardItem = ref(null)
const newRewardItem = ref({ name: '', description: '', category: '', points_cost: 10, stock: '', valid_from: '', valid_until: '', is_active: true })
const rewardItemImageFile = ref(null) // the cropped 4:3 JPEG that gets uploaded
const rewardImageDragging = ref(false)
const rewardImageOriginal = ref(null)  // the picked file, kept so the crop can be redone from it
const rewardImagePreview  = ref('')    // object URL of rewardItemImageFile
const rewardCropSource    = ref(null)  // File or image URL open in the crop dialog
const rewardCropper       = ref(null)
const applyingRewardCrop  = ref(false)
// New crop first; while editing without one, the reward's current image.
const rewardImagePreviewUrl = computed(() => rewardImagePreview.value || editingRewardItem.value?.image_url || '')
const rewardItemError = ref('')
const savingRewardItem = ref(false)

const adminRedemptions = ref([])
const redemptionsPage = ref(1)
const redemptionsPerPage = ref(20)
const redemptionsTotal = ref(0)
const redemptionsLastPage = ref(1)

const editingUser = ref(null)
const editingMachine = ref(null)
const showAddMachine = ref(false)
const savingMachine = ref(false)

const reportDateFrom = ref('')
const reportDateTo = ref('')
const showEmailReportModal = ref(false)
const emailReportAddress = ref('')
const sendingReportEmail = ref(false)
const emailReportError = ref('')

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
const collectionCandidates = ref([])
const requestingCollection = ref(false)

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
      title: { display: true, text: 'Carbon Saved (kg)', color: '#6b7280', font: { size: 11 } },
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
  { id: 'dashboard',    icon: PhChartBar,        label: 'Dashboard' },
  { id: 'machines',     icon: PhFactory,         label: 'Machines' },
  { id: 'rewards',      icon: PhGift,            label: 'Rewards' },
  { id: 'users',        icon: PhUsers,           label: 'Users' },
  { id: 'transactions', icon: PhReceipt,         label: 'Transactions' },
  { id: 'sessions',     icon: PhClipboardText,   label: 'Sessions' },
  { id: 'audit',        icon: PhListChecks,      label: 'Audit Log' },
  { id: 'detection',    icon: PhMagnifyingGlass, label: 'Detection Review' },
]

// Reopen the menu (and Detection Review sub-view) named in the URL, so a
// refresh stays where the admin was; see the watch that writes it back below.
{
  const saved = parseAdminQuery(route.query, navItems.map(item => item.id))
  activeTab.value = saved.tab
  detectionView.value = saved.detectionView
}

const binTypes = [
  { id: 'aluminum', label: 'Aluminum' },
  { id: 'plastic',  label: 'Plastic'  },
  { id: 'glass',    label: 'Glass'    },
  { id: 'paper',    label: 'Paper'    },
]

const rewardMaterials = [
  { key: 'plastic',  label: 'Plastic Bottle (pts)' },
  { key: 'aluminum', label: 'Aluminium Can (pts)'  },
  { key: 'paper',    label: 'Paper / Card (pts)'   },
  { key: 'glass',    label: 'Glass Bottle (pts)'   },
]

const overviewMaterials = [
  { key: 'plastic',  label: 'PLASTIC BOTTLES'   },
  { key: 'aluminum', label: 'ALUMINIUM CANS'    },
  { key: 'paper',    label: 'PAPER / RECYCLABLES'},
  { key: 'glass',    label: 'GLASS BOTTLES'     },
]


// ── Computed ──
const currentNavItem = computed(() => navItems.find(n => n.id === activeTab.value))

const statsCards = computed(() => [
  { icon: PhUsers,           label: 'Total Users',    value: statsData.value.total_users    || 0 },
  { icon: PhFactory,         label: 'Machines',       value: statsData.value.total_machines || 0 },
  { icon: PhArrowsClockwise, label: 'Active Sessions',value: statsData.value.active_sessions || 0 },
  { icon: PhGlobe,           label: 'Carbon Saved (kg)', value: statsData.value.total_carbon_saved_kg || 0 },
  { icon: PhStar,            label: 'Points Issued',  value: (statsData.value.total_points_given || 0).toLocaleString(), color: 'var(--accent-green)' },
  { icon: PhPackage,         label: 'Transactions',  value: statsData.value.total_transactions || 0 },
])

const activeMachines = computed(() =>
  adminMachines.value.filter(m => m.status === 'active').length
)

// Heartbeat-based machine state (A02/A05). nowTick is bumped by the 1 s clock
// so a machine turns offline on screen without waiting for a refetch.
const nowTick = ref(new Date())
const historyMachine = ref(null) // machine whose Service History modal is open (A06)
function openHistoryFor(machineId) {
  const machine = adminMachines.value.find(m => m.id === machineId)
  if (machine) historyMachine.value = machine
}
const machineSummary = computed(() => connectivitySummary(adminMachines.value, nowTick.value))
const machineAlerts  = computed(() => buildMachineAlerts(adminMachines.value, nowTick.value))

function relativeTime(ts) {
  const t = ts ? new Date(ts).getTime() : NaN
  if (!Number.isFinite(t)) return '—'
  const s = Math.max(0, Math.round((nowTick.value.getTime() - t) / 1000))
  if (s < 60) return `${s}s ago`
  const m = Math.round(s / 60)
  if (m < 60) return `${m} min ago`
  const h = Math.round(m / 60)
  if (h < 48) return `${h} h ago`
  return `${Math.round(h / 24)} days ago`
}

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

  // Heartbeat-based once any kiosk reports; until then, the registered status.
  const summary        = machineSummary.value
  const totalMachines  = machines.length
  const onlineMachines = summary.anyReported ? summary.online : machines.filter(m => m.status === 'active').length
  const machineColor   = summary.anyReported ? (onlineMachines > 0 ? 'green' : 'red') : 'yellow'
  const machineWord    = summary.anyReported ? 'ONLINE' : 'REGISTERED'

  const cameraColor = activeSessions > 0 ? 'green' : 'yellow'
  const cameraValue = activeSessions > 0 ? `LIVE (${activeSessions})` : 'STANDBY'

  const rewardOk    = Object.values(savedRewardValues.value).every(v => v > 0)
  const rewardColor = rewardOk ? 'green' : 'yellow'

  return [
    { label: 'Camera',       value: cameraValue,                           color: cameraColor  },
    { label: 'Machines',  value: `${onlineMachines}/${totalMachines} ${machineWord}`, color: machineColor },
    { label: 'Reward Engine',value: rewardOk ? 'OK' : 'CHECK CONFIG',      color: rewardColor  },
    { label: 'Plastic Bin',  value: binLabel(plastic,  binColor(plastic)),  color: binColor(plastic)  },
    { label: 'Aluminum Bin', value: binLabel(aluminum, binColor(aluminum)), color: binColor(aluminum) },
    { label: 'Paper Bin',    value: binLabel(paper,    binColor(paper)),    color: binColor(paper)    },
    { label: 'Glass Bin',    value: binLabel(glass,    binColor(glass)),    color: binColor(glass)    },
  ]
})

// ── Helpers ──

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

// ── Transactions table display ──
const txItemLabel = itemLabel
const txReason = resultReason
function txConfClass(conf) { return 'tx-conf-' + confidenceLevel(conf) }
function txPct(part) { return percentOf(part, txSummary.value.items) }
function txDate(ts) {
  if (!ts) return '—'
  return new Date(ts).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}
function txTime(ts) {
  if (!ts) return ''
  return new Date(ts).toLocaleTimeString('en-MY', { hour: 'numeric', minute: '2-digit', hour12: true })
}
function txNum(n) { return Number(n || 0).toLocaleString('en-MY') }
function txUserName(user) {
  if (!user) return 'Guest'
  return user.name || formatUserId(user)
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
      const binsNeedingCollection = res.data.stats?.full_bins || []
      fullBins.value = binsNeedingCollection
      // Keep eligibility separate from the dismissible alert list. Dismissing
      // a warning does not change the physical bin level.
      collectionCandidates.value = binsNeedingCollection
    } else if (tab === 'transactions') {
      if (showSpinner) loadingTransactions.value = true
      // The machine filter needs the machine list even if the Machines tab was never opened.
      if (!adminMachines.value.length) {
        try {
          const mres = await api.get('/admin/machines')
          adminMachines.value = (mres.data.machines || []).map(normalizeMachine)
        } catch { /* filter simply stays on "All machines" */ }
      }
      const res = await api.get('/admin/transactions', { params: {
        page: txPage.value, per_page: txPerPage.value,
        search: txSearch.value || undefined, status: txFilter.value || undefined,
        machine_id: txMachine.value || undefined,
        date_from: txDateFrom.value || undefined, date_to: txDateTo.value || undefined,
      } })
      transactions.value = res.data.transactions?.data || []
      txTotal.value     = res.data.transactions?.total ?? 0
      txSummary.value   = { items: 0, accepted: 0, rejected: 0, points: 0, ...(res.data.summary || {}) }
      txLastPage.value  = res.data.transactions?.last_page ?? 1
    } else if (tab === 'users') {
      if (showSpinner) loadingUsers.value = true
      const res = await api.get('/admin/users', { params: {
        page: usersPage.value, per_page: usersPerPage.value, search: userSearch.value || undefined,
        sort: usersSortColumn.value, direction: usersSortDirection.value,
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
    } else if (tab === 'audit') {
      if (showSpinner) loadingAudit.value = true
      const res = await api.get('/admin/logs', { params: {
        page: auditPage.value, per_page: auditPerPage.value,
        search: auditSearch.value || undefined, action: auditAction.value || undefined,
        date_from: auditDateFrom.value || undefined, date_to: auditDateTo.value || undefined,
      } })
      auditLogs.value     = res.data.logs?.data || []
      auditActions.value  = res.data.actions || []
      auditTotal.value    = res.data.logs?.total ?? 0
      auditLastPage.value = res.data.logs?.last_page ?? 1
    } else if (tab === 'detection') {
      if (showSpinner) loadingDetectionLogs.value = true
      const requestStartedAt = performance.now()
      const seq = ++detectionRequestSeq
      const res = await api.get('/admin/detection-logs', { params: {
        page: detectionPage.value, per_page: detectionPerPage.value,
        date_from: activeDetectionDateFilter.value.from || undefined,
        date_to: activeDetectionDateFilter.value.to || undefined,
        review_status: detectionReviewStatus.value,
      } })
      // Saves, refills and Undo can overlap: a list request answered after a
      // newer one was sent is stale, so it must not overwrite that one.
      if (seq !== detectionRequestSeq) return
      // Keep cards reviewed optimistically out of Pending until a response
      // that is guaranteed to reflect their save (see saveDetectionReview).
      hiddenDetectionIds = pruneSettled(hiddenDetectionIds, requestStartedAt)
      const serverLogs = res.data.detection_logs?.data || []
      const shownLogs = detectionView.value === 'pending' ? filterHidden(serverLogs, hiddenDetectionIds) : serverLogs
      detectionLogs.value     = shownLogs
      detectionTotal.value    = Math.max(0, (res.data.detection_logs?.total ?? 0) - (serverLogs.length - shownLogs.length))
      detectionLastPage.value = res.data.detection_logs?.last_page ?? 1
      releaseThumbnails(detectionLogs.value)
      detectionLogs.value.forEach(loadThumbnail)
      detectionCache[detectionCacheKey()] = {
        logs: detectionLogs.value, total: detectionTotal.value,
        lastPage: detectionLastPage.value, fetchedAt: Date.now(),
      }
    } else if (tab === 'rewards') {
      if (showSpinner) loadingRewardItems.value = true
      const res = await api.get('/admin/reward-items', { params: {
        page: rewardItemsPage.value, per_page: rewardItemsPerPage.value,
      } })
      rewardItems.value        = res.data.reward_items?.data || []
      rewardItemsTotal.value   = res.data.reward_items?.total ?? 0
      rewardItemsLastPage.value = res.data.reward_items?.last_page ?? 1
      const redemptionsRes = await api.get('/admin/redemptions', { params: {
        page: redemptionsPage.value, per_page: redemptionsPerPage.value,
      } })
      adminRedemptions.value      = redemptionsRes.data.redemptions?.data || []
      redemptionsTotal.value      = redemptionsRes.data.redemptions?.total ?? 0
      redemptionsLastPage.value   = redemptionsRes.data.redemptions?.last_page ?? 1
    }
    tabFetchedAt[tab] = Date.now()
  } catch (err) {
    const msg = err.response?.data?.message || err.message || 'Failed to load data'
    tabError.value = msg
    console.error(`fetchTabData(${tab}):`, err.response?.data || err.message)
  } finally {
    const flags = { loadingStats, loadingUsers, loadingMachines, loadingSessions, loadingAudit, loadingTransactions, loadingDetectionLogs, loadingRewardItems }
    const flagName = resolveLoadingFlag(tab)
    if (flagName) flags[flagName].value = false
  }
}

/**
 * Frees the object URLs of thumbnails that are no longer on screen — without
 * this, paging through hundreds of exhibition photos pins every blob in memory
 * for the lifetime of the page. Rows still being displayed are kept so the 30s
 * auto-refresh doesn't re-download (and visibly re-flash) the whole gallery;
 * pass no argument to release everything.
 */
function releaseThumbnails(keepLogs = []) {
  const keep = new Set(keepLogs.map(log => String(log.id)))
  for (const [id, url] of Object.entries(thumbnails.value)) {
    if (keep.has(id)) continue
    if (url) URL.revokeObjectURL(url)
    delete thumbnails.value[id]
  }
}

// A mock classification (or a row whose guest flow never uploaded an image) has
// no real photo behind it — the material/confidence were fabricated, so there
// is nothing an admin can honestly mark correct or incorrect.
function isReviewable(log) {
  return !log.is_mock && !!log.image_path
}

function reviewDisabledReason(log) {
  return isReviewable(log) ? '' : t('admin.detectionReview.notReviewable')
}

function normalizedPrediction(log) {
  return String(log?.ai_detected_type || '').trim().toLowerCase()
}

function isUnknownPrediction(log) {
  return normalizedPrediction(log) === 'unknown' || normalizedPrediction(log) === ''
}

function correctDisabledReason(log) {
  if (!isReviewable(log)) return reviewDisabledReason(log)
  return isUnknownPrediction(log) ? t('admin.detectionReview.unknownMustAssign') : ''
}

function materialLabel(value) {
  const match = detectionMaterials.value.find(material => material.value === value)
  return match?.label || value || '—'
}

async function loadThumbnail(log) {
  if (thumbnails.value[log.id] !== undefined) return
  thumbnails.value[log.id] = null
  // Guest rows that never uploaded a photo would 404 every time — don't spend a
  // request (or a console error) on a row we already know has no image.
  if (!log.image_path) return
  try {
    const res = await api.get(`/admin/detection-logs/${log.id}/image`, { responseType: 'blob' })
    thumbnails.value[log.id] = URL.createObjectURL(res.data)
  } catch {
    thumbnails.value[log.id] = null
  }
}

function showDetectionUndo(id, message) {
  clearTimeout(detectionUndoTimer)
  detectionUndo.value = { show: true, id, message, loading: false, key: detectionUndo.value.key + 1 }
  detectionUndoTimer = setTimeout(() => {
    detectionUndo.value.show = false
  }, 5000)
}

function hideDetectionUndo(id) {
  if (detectionUndo.value.id !== id) return // a newer review's toast is up
  clearTimeout(detectionUndoTimer)
  detectionUndo.value.show = false
}

// A reviewed item moves from Pending into History — every cached sub-view
// could now be stale, not just the one currently on screen.
function invalidateDetectionCache() {
  tabFetchedAt.detection = 0
  for (const key of Object.keys(detectionCache)) delete detectionCache[key]
}

// Optimistic: the card leaves the list and the Undo toast shows immediately
// (the cards below slide up with the list's move transition); the PATCH runs
// in the background. On failure the card goes back where it was and the Undo
// toast gives way to an error toast. Several saves may run at once; the list
// is refilled from the server once the last one settles.
async function saveDetectionReview(log, payload) {
  if (hiddenDetectionIds.has(log.id)) return false // already being saved
  const listKey = detectionCacheKey()
  hiddenDetectionIds.set(log.id, null)
  const removed = removeLog(detectionLogs.value, log.id)
  detectionLogs.value = removed.logs
  if (removed.log) detectionTotal.value = Math.max(0, detectionTotal.value - 1)
  invalidateDetectionCache()
  showDetectionUndo(
    log.id,
    payload.ground_truth_correct
      ? t('admin.detectionReview.markedCorrect')
      : t('admin.detectionReview.markedIncorrect')
  )

  const request = api.patch(`/admin/detection-logs/${log.id}`, payload).then(() => true, () => false)
  detectionSaves.set(log.id, request)
  const saved = await request
  detectionSaves.delete(log.id)

  if (saved) {
    hiddenDetectionIds.set(log.id, performance.now())
  } else {
    hiddenDetectionIds.delete(log.id)
    // Only if the admin is still looking at the same page/filters.
    if (removed.log && detectionCacheKey() === listKey) {
      detectionLogs.value = restoreLog(detectionLogs.value, removed.log, removed.index)
      detectionTotal.value += 1
    }
    hideDetectionUndo(log.id)
    showToast(t('admin.detectionReview.saveFailed'), 'error')
  }

  invalidateDetectionCache()
  // Not awaited: the card is already gone, this only pulls in the next items.
  if (![...hiddenDetectionIds.values()].includes(null)) fetchTabData('detection', false)
  return saved
}

async function markCorrect(log) {
  if (!isReviewable(log) || isUnknownPrediction(log)) return
  await saveDetectionReview(log, { ground_truth_correct: true })
}

// Touch swipe on a pending card (directives/swipe.js): right = Correct,
// left = Incorrect. Returning false snaps the card back into the list.
async function swipeCorrect(log) {
  const reason = correctDisabledReason(log)
  if (reason) {
    showToast(reason, 'error')
    return false
  }
  return saveDetectionReview(log, { ground_truth_correct: true })
}

function swipeIncorrect(log) {
  if (!isReviewable(log)) {
    showToast(reviewDisabledReason(log), 'error')
    return false
  }
  openIncorrectReview(log)
  return false
}

function openIncorrectReview(log) {
  if (!isReviewable(log)) return
  reviewingDetection.value = log
}

function closeIncorrectReview() {
  reviewingDetection.value = null
}

function openDetectionViewer(log) {
  viewingDetection.value = log
}

function closeDetectionViewer() {
  viewingDetection.value = null
}

// Review straight from the full-size viewer, same rules as the card buttons.
async function viewerMarkCorrect() {
  const log = viewingDetection.value
  if (!log) return
  closeDetectionViewer()
  await markCorrect(log)
}

function viewerMarkIncorrect() {
  const log = viewingDetection.value
  if (!log) return
  closeDetectionViewer()
  openIncorrectReview(log)
}

// Tapping a material in the modal saves it right away. The modal closes at
// once (so a second tap can't hit another material) and the card leaves the
// list; it comes back with an error toast if the save fails (see
// saveDetectionReview), and the Undo toast covers a mis-tap.
function saveIncorrectReview(material) {
  const log = reviewingDetection.value
  if (!log) return
  closeIncorrectReview()
  saveDetectionReview(log, { ground_truth_correct: false, ground_truth_label: material })
}

async function undoDetectionReview() {
  // The toast can be replaced by a newer review while we await below, so work
  // with this review's id throughout rather than re-reading detectionUndo.
  const id = detectionUndo.value.id
  if (!id || detectionUndo.value.loading) return
  clearTimeout(detectionUndoTimer)
  detectionUndo.value.loading = true
  // Pressed before the server confirmed the save: wait for it first. If the
  // save failed, its handler already put the card back and showed the error.
  const pendingSave = detectionSaves.get(id)
  if (pendingSave && !(await pendingSave)) return
  try {
    await api.patch(`/admin/detection-logs/${id}`, { undo: true })
    hideDetectionUndo(id)
    hiddenDetectionIds.delete(id) // back in Pending: show it again
    invalidateDetectionCache()
    await fetchTabData('detection', false)
    showToast(t('admin.detectionReview.undoSuccess'))
  } catch {
    if (detectionUndo.value.id === id) detectionUndo.value.loading = false
    showToast(t('admin.detectionReview.undoFailed'), 'error')
  }
}

async function exportDetectionLogsExcel() {
  if (detectionView.value !== 'history') return
  try {
    // Export only the reviewed rows represented by the active History filters.
    // The backend also enforces reviewed/non-mock data as a second guard.
    await downloadExcel(api, '/admin/detection-logs/export', {
      date_from: activeDetectionDateFilter.value.from,
      date_to: activeDetectionDateFilter.value.to,
      review_status: detectionReviewStatus.value,
    }, 'detection_reviews')
  } catch {
    showToast(t('admin.detectionReview.exportFailed'), 'error')
  }
}

// ── Excel export per admin table ──
// Each entry: [endpoint, the same filters the table is showing, file prefix].
const TABLE_EXPORTS = {
  transactions: () => ['/admin/transactions/export', {
    search: txSearch.value, status: txFilter.value, machine_id: txMachine.value,
    date_from: txDateFrom.value, date_to: txDateTo.value,
  }, 'rvm_transactions'],
  users: () => ['/admin/users/export', {
    search: userSearch.value, sort: usersSortColumn.value, direction: usersSortDirection.value,
  }, 'rvm_users'],
  machines: () => ['/admin/machines/export', {}, 'rvm_machines'],
  sessions: () => ['/admin/sessions/export', { search: sessionsSearch.value }, 'rvm_sessions'],
  audit: () => ['/admin/logs/export', {
    search: auditSearch.value, action: auditAction.value,
    date_from: auditDateFrom.value, date_to: auditDateTo.value,
  }, 'rvm_audit_log'],
  rewardItems: () => ['/admin/reward-items/export', {}, 'rvm_reward_catalog'],
  redemptions: () => ['/admin/redemptions/export', {}, 'rvm_redemptions'],
}
const exportingTable = ref('')
async function exportTable(key) {
  if (exportingTable.value) return
  exportingTable.value = key
  try {
    const [path, params, prefix] = TABLE_EXPORTS[key]()
    await downloadExcel(api, path, params, prefix)
  } catch {
    showToast('Export failed. Please try again.', 'error')
  } finally {
    exportingTable.value = ''
  }
}

async function fetchCashRedeemSettings() {
  try {
    const res = await api.get('/admin/cash-redeem-settings')
    cashRedeemSettings.value = { ...cashRedeemSettings.value, ...res.data.settings }
  } catch {}
}

async function saveCashRedeemSettings() {
  savingCashRedeemSettings.value = true
  try {
    const res = await api.put('/admin/cash-redeem-settings', cashRedeemSettings.value)
    cashRedeemSettings.value = res.data.settings
    showToast('Cash redeem settings updated.')
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to update cash redeem settings.', 'error')
  } finally {
    savingCashRedeemSettings.value = false
  }
}

async function fetchCompactorSettings() {
  try {
    const res = await api.get('/admin/compactor-settings')
    compactorSettings.value = { ...compactorSettings.value, ...res.data.settings }
  } catch {}
}

async function saveCompactorSettings() {
  const { plastic, aluminum } = compactorSettings.value
  if (![plastic, aluminum].every((v) => Number.isInteger(v) && v >= 1 && v <= 10)) {
    showToast('Max per session must be a whole number from 1 to 10.', 'error')
    return
  }
  savingCompactorSettings.value = true
  try {
    const res = await api.put('/admin/compactor-settings', { plastic, aluminum })
    compactorSettings.value = res.data.settings
    showToast('Compactor settings updated.')
  } catch (e) {
    showToast(e.response?.data?.message || 'Failed to update compactor settings.', 'error')
  } finally {
    savingCompactorSettings.value = false
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

// Mirror the open menu into the URL (replace, not push: Back still leaves the
// admin panel instead of stepping through every menu visited). Immediate, so
// an invalid ?tab= from an old link is cleaned up on load too.
watch([activeTab, detectionView], ([tab, view]) => {
  const query = buildAdminQuery({ tab, detectionView: view })
  if (query.tab === route.query.tab && query.view === route.query.view) return
  router.replace({ query })
}, { immediate: true })

// The other direction: a ?tab= edited in the address bar (or Back/Forward)
// changes the route without remounting this view, so follow it here.
watch(() => [route.query.tab, route.query.view], () => {
  const wanted = parseAdminQuery(route.query, navItems.map(item => item.id))
  if (wanted.detectionView !== detectionView.value) detectionView.value = wanted.detectionView
  if (wanted.tab !== activeTab.value) switchTab(wanted.tab)
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
function changeUsersPerPage(perPage) {
  usersPerPage.value = perPage
  usersPage.value = 1
  fetchTabData('users', true)
}
function sortUsersBy(column) {
  if (usersSortColumn.value === column) {
    usersSortDirection.value = usersSortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    usersSortColumn.value = column
    usersSortDirection.value = 'asc'
  }
  usersPage.value = 1
  fetchTabData('users', true)
}
function usersSortIndicator(column) {
  if (usersSortColumn.value !== column) return ''
  return usersSortDirection.value === 'asc' ? ' ▲' : ' ▼'
}

function searchTransactions() {
  txPage.value = 1
  fetchTabData('transactions', true)
}
function filterTransactions() {
  txPage.value = 1
  fetchTabData('transactions', true)
}
function setTxResult(value) {
  if (txFilter.value === value) return
  txFilter.value = value
  filterTransactions()
}
// Search as the admin types (Enter still searches at once).
let txSearchTimer = null
function debouncedTxSearch() {
  clearTimeout(txSearchTimer)
  txSearchTimer = setTimeout(searchTransactions, 400)
}
function clearTransactionFilters() {
  clearTimeout(txSearchTimer)
  txSearch.value = ''
  txFilter.value = ''
  txMachine.value = ''
  txDateFrom.value = ''
  txDateTo.value = ''
  filterTransactions()
}
function goToTxPage(page) {
  txPage.value = page
  fetchTabData('transactions', true)
}
function changeTxPerPage(perPage) {
  txPerPage.value = perPage
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
function changeSessionsPerPage(perPage) {
  sessionsPerPage.value = perPage
  sessionsPage.value = 1
  fetchTabData('sessions', true)
}

function searchAudit() {
  auditPage.value = 1
  fetchTabData('audit', true)
}
function goToAuditPage(page) {
  auditPage.value = page
  fetchTabData('audit', true)
}
function changeAuditPerPage(perPage) {
  auditPerPage.value = perPage
  auditPage.value = 1
  fetchTabData('audit', true)
}

function goToRewardItemsPage(page) {
  rewardItemsPage.value = page
  fetchTabData('rewards')
}

function changeRewardItemsPerPage(perPage) {
  rewardItemsPerPage.value = perPage
  rewardItemsPage.value = 1
  fetchTabData('rewards')
}

function goToRedemptionsPage(page) {
  redemptionsPage.value = page
  fetchTabData('rewards')
}

function changeRedemptionsPerPage(perPage) {
  redemptionsPerPage.value = perPage
  redemptionsPage.value = 1
  fetchTabData('rewards')
}

function goToDetectionPage(page) {
  detectionPage.value = page
  loadDetectionData(true)
}
function changeDetectionPerPage(perPage) {
  detectionPerPage.value = perPage
  detectionPage.value = 1
  loadDetectionData(true)
}
function setDetectionDatePreset(preset) {
  const filter = activeDetectionDateFilter.value
  filter.preset = preset

  const range = detectionDateRange(preset)
  if (!range) return

  filter.from = range.from
  filter.to = range.to
  detectionPage.value = 1
  loadDetectionData(true)
}
function setDetectionCustomDate(field, value) {
  const filter = activeDetectionDateFilter.value
  filter[field] = value

  // Keep the custom range valid without asking the admin to correct the other
  // field manually after choosing dates in reverse order.
  if (filter.from && filter.to && filter.from > filter.to) {
    if (field === 'from') filter.to = filter.from
    else filter.from = filter.to
  }

  detectionPage.value = 1
  loadDetectionData(true)
}
function setDetectionView(view) {
  if (detectionView.value === view) return
  detectionView.value = view
  detectionPage.value = 1
  loadDetectionData(true)
}
function setDetectionHistoryStatus(status) {
  if (detectionHistoryStatus.value === status) return
  detectionHistoryStatus.value = status
  detectionPage.value = 1
  loadDetectionData(true)
}

// ── Admin Controls ──
async function exportExcel() {
  try {
    const res = await api.get('/admin/export-excel', {
      responseType: 'blob',
      params: { date_from: reportDateFrom.value || undefined, date_to: reportDateTo.value || undefined },
    })
    const url = URL.createObjectURL(res.data)
    const a = document.createElement('a')
    a.href = url
    a.download = `rvm_report_${new Date().toISOString().slice(0, 10)}.xlsx`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch {
    showToast('Export failed. Please try again.', 'error')
  }
}

function openEmailReportModal() {
  emailReportAddress.value = ''
  emailReportError.value = ''
  showEmailReportModal.value = true
}

async function sendReportEmail() {
  if (!emailReportAddress.value.trim()) {
    emailReportError.value = 'Email address is required.'
    return
  }
  sendingReportEmail.value = true
  emailReportError.value = ''
  try {
    const res = await api.post('/admin/export-excel/email', {
      email: emailReportAddress.value.trim(),
      date_from: reportDateFrom.value || undefined,
      date_to: reportDateTo.value || undefined,
    })
    showEmailReportModal.value = false
    showToast(res.data.message || 'Report emailed.')
  } catch (e) {
    const errors = e.response?.data?.errors
    emailReportError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Failed to send report.')
  } finally {
    sendingReportEmail.value = false
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
  if (!collectionCandidates.value.length || requestingCollection.value) {
    showToast('No bins are at or above 90%.', 'error')
    return
  }

  const details = collectionCandidates.value.map((machine) => {
    const bins = ['aluminum', 'plastic', 'glass', 'paper']
      .filter(type => Number(machine[`${type}_level`]) >= 90)
      .map(type => `${capitalize(type)} ${machine[`${type}_level`]}%`)
      .join(', ')
    return `${machine.name}: ${bins}`
  }).join('; ')

  if (!(await askConfirm(`Send a collection request for ${collectionCandidates.value.length} machine(s)? ${details}`, 'warning'))) return

  requestingCollection.value = true
  try {
    // Deliberately a separate endpoint from resetAllAlerts() — this only logs
    // the request, it must not zero out bin levels (the bin isn't actually
    // empty yet, someone just needs to go collect it).
    const res = await api.post('/admin/request-bin-collection')
    showToast(`Bin collection request sent for ${res.data.affected} machine(s).`)
  } catch (error) {
    showToast(error.response?.data?.message || 'Failed to send collection request.', 'error')
  } finally {
    requestingCollection.value = false
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

// Suspend / reactivate (A08). Suspending signs the user out everywhere.
async function toggleSuspend(user) {
  const suspending = user.account_status !== 'suspended'
  const question = suspending
    ? `Suspend "${user.name}"? They will be signed out and cannot log in until reactivated.`
    : `Reactivate "${user.name}"?`
  if (!(await askConfirm(question))) return
  try {
    const res = await api.put(`/admin/users/${user.id}`, { account_status: suspending ? 'suspended' : 'active' })
    const idx = users.value.findIndex(u => u.id === user.id)
    if (idx > -1) users.value[idx] = { ...users.value[idx], account_status: res.data.user?.account_status ?? (suspending ? 'suspended' : 'active') }
    showToast(suspending ? `"${user.name}" suspended.` : `"${user.name}" reactivated.`)
  } catch (err) {
    showToast(err?.response?.data?.message || 'Failed to update the account status.', 'error')
  }
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

// ── Kiosk maintenance (scan the machine's live QR to prove presence, then
// tell it to close its own Chromium — see AdminController::maintainMachine) ──
const maintainingMachine = ref(null)
const maintTokenInput    = ref('')
const maintError         = ref('')
const maintLoading       = ref(false)
const maintCameraActive  = ref(false)
const maintVideoRef      = ref(null)
const maintCanvasRef     = ref(null)
let maintStream       = null
let maintScanInterval = null

function openMaintenance(machine) {
  maintainingMachine.value = machine
  maintTokenInput.value = ''
  maintError.value = ''
}

function closeMaintenance() {
  stopMaintCamera()
  maintainingMachine.value = null
}

async function toggleMaintCamera() {
  if (maintCameraActive.value) stopMaintCamera()
  else await startMaintCamera()
}

async function startMaintCamera() {
  maintError.value = ''
  if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
    maintError.value = 'Camera access requires HTTPS.'
    return
  }
  try {
    maintStream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: { ideal: 'environment' }, width: { ideal: 640 }, height: { ideal: 640 } },
    })
    maintVideoRef.value.srcObject = maintStream
    maintCameraActive.value = true
    maintScanInterval = setInterval(scanMaintFrame, 200)
  } catch (e) {
    maintError.value = 'Could not access the camera: ' + e.message
  }
}

function stopMaintCamera() {
  clearInterval(maintScanInterval)
  maintScanInterval = null
  if (maintStream) {
    maintStream.getTracks().forEach(track => track.stop())
    maintStream = null
  }
  if (maintVideoRef.value) maintVideoRef.value.srcObject = null
  maintCameraActive.value = false
}

function scanMaintFrame() {
  const video = maintVideoRef.value
  const canvas = maintCanvasRef.value
  if (!video || !canvas || video.readyState < 2) return

  const size = Math.min(video.videoWidth, video.videoHeight)
  canvas.width  = size
  canvas.height = size
  const ctx = canvas.getContext('2d')
  const offsetX = (video.videoWidth  - size) / 2
  const offsetY = (video.videoHeight - size) / 2
  ctx.drawImage(video, offsetX, offsetY, size, size, 0, 0, size, size)

  const imageData = ctx.getImageData(0, 0, size, size)
  const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' })
  if (code?.data) {
    stopMaintCamera()
    maintTokenInput.value = extractQrToken(code.data)
    submitMaintenance()
  }
}

async function submitMaintenance() {
  const token = extractQrToken(maintTokenInput.value)
  if (!token) return
  maintLoading.value = true
  maintError.value = ''
  try {
    const res = await api.post(`/admin/machines/${maintainingMachine.value.id}/maintenance`, { qr_token: token })
    if (res.data.success) {
      showToast(`Kiosk for "${maintainingMachine.value.name}" closed for maintenance.`)
      closeMaintenance()
    } else {
      maintError.value = res.data.message || 'Failed to trigger maintenance.'
    }
  } catch (e) {
    maintError.value = e.response?.data?.message || 'Failed to trigger maintenance.'
  } finally {
    maintLoading.value = false
  }
}

function openDatePicker(event) {
  const target = event.currentTarget
  const input = target.matches?.('input') ? target : target.querySelector?.('input')
  if (!input) return
  try {
    if (typeof input.showPicker === 'function') input.showPicker()
    else input.focus()
  } catch {
    input.focus()
  }
}

function setRewardImageFile(file) {
  rewardImageDragging.value = false
  if (!file) {
    rewardItemImageFile.value = null
    return
  }
  if (!['image/jpeg', 'image/png'].includes(file.type)) {
    rewardItemImageFile.value = null
    rewardItemError.value = 'Please choose a PNG or JPG image.'
    return
  }
  // Not uploaded as-is: it goes through the crop dialog first.
  rewardImageOriginal.value = file
  rewardCropSource.value = file
  rewardItemError.value = ''
}

function handleRewardImageChange(event) {
  setRewardImageFile(event.target.files?.[0] || null)
  event.target.value = '' // picking the same file again still fires change
}

function setRewardImagePreview(file) {
  if (rewardImagePreview.value) URL.revokeObjectURL(rewardImagePreview.value)
  rewardImagePreview.value = file ? URL.createObjectURL(file) : ''
}

function resetRewardImage() {
  rewardItemImageFile.value = null
  rewardImageOriginal.value = null
  rewardCropSource.value = null
  setRewardImagePreview(null)
}

// Re-crop from the full original (never from an earlier crop, which would lose detail).
function adjustRewardImage() {
  rewardCropSource.value = rewardImageOriginal.value || editingRewardItem.value?.image_url || null
}

function closeRewardCrop() {
  rewardCropSource.value = null
}

async function applyRewardCrop() {
  applyingRewardCrop.value = true
  try {
    const file = await rewardCropper.value?.exportFile()
    if (!file) {
      rewardItemError.value = 'This image could not be cropped. Please choose another one.'
      rewardCropSource.value = null
      return
    }
    rewardItemImageFile.value = file
    setRewardImagePreview(file)
    rewardItemError.value = ''
    rewardCropSource.value = null
  } finally {
    applyingRewardCrop.value = false
  }
}

function handleRewardImageDrop(event) {
  setRewardImageFile(event.dataTransfer?.files?.[0] || null)
}

function openAddRewardItem() {
  newRewardItem.value = { name: '', description: '', category: '', points_cost: 10, stock: '', valid_from: '', valid_until: '', is_active: true }
  resetRewardImage()
  rewardItemError.value = ''
  showAddRewardItem.value = true
}

function openEditRewardItem(item) {
  editingRewardItem.value = {
    ...item,
    valid_from: toDatetimeLocalValue(item.valid_from),
    valid_until: toDatetimeLocalValue(item.valid_until),
  }
  resetRewardImage()
  rewardItemError.value = ''
}

function buildRewardItemFormData(source) {
  // Always append the nullable fields, even empty — the backend's global
  // ConvertEmptyStringsToNull middleware turns '' into null before validation,
  // so this is how an admin clears stock/valid_from/valid_until/description/
  // category on an edit (omitting the key entirely, as before, meant Laravel's
  // validate() never saw it, so $item->update() left the old value in place —
  // an item could gain a stock cap or an expiry but never lose one).
  const fd = new FormData()
  fd.append('name', source.name.trim())
  fd.append('description', source.description || '')
  fd.append('category', source.category || '')
  fd.append('points_cost', source.points_cost)
  fd.append('stock', source.stock === '' || source.stock === null || source.stock === undefined ? '' : source.stock)
  fd.append('valid_from', source.valid_from || '')
  fd.append('valid_until', source.valid_until || '')
  fd.append('is_active', source.is_active ? '1' : '0')
  if (rewardItemImageFile.value) fd.append('image', rewardItemImageFile.value)
  return fd
}

async function addRewardItem() {
  if (!newRewardItem.value.name?.trim()) {
    rewardItemError.value = 'Name is required.'
    return
  }
  savingRewardItem.value = true
  rewardItemError.value = ''
  try {
    const res = await api.post('/admin/reward-items', buildRewardItemFormData(newRewardItem.value), {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    showAddRewardItem.value = false
    showToast(`Reward "${res.data.reward_item.name}" added.`)
    // Refetch rather than unshift locally: the list is now paginated, so a
    // local splice would leave rewardItemsTotal/rewardItemsLastPage stale.
    rewardItemsPage.value = 1
    await fetchTabData('rewards')
  } catch (e) {
    const errors = e.response?.data?.errors
    rewardItemError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Failed to add reward.')
  } finally {
    savingRewardItem.value = false
  }
}

async function saveRewardItem() {
  if (!editingRewardItem.value.name?.trim()) {
    rewardItemError.value = 'Name is required.'
    return
  }
  savingRewardItem.value = true
  rewardItemError.value = ''
  try {
    const fd = buildRewardItemFormData(editingRewardItem.value)
    fd.append('_method', 'PUT')
    const res = await api.post(`/admin/reward-items/${editingRewardItem.value.id}`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    const idx = rewardItems.value.findIndex(r => r.id === editingRewardItem.value.id)
    if (idx > -1) rewardItems.value[idx] = res.data.reward_item
    editingRewardItem.value = null
    showToast(`Reward "${res.data.reward_item.name}" saved.`)
  } catch (e) {
    const errors = e.response?.data?.errors
    rewardItemError.value = errors ? Object.values(errors).flat().join(' ') : (e.response?.data?.message || 'Failed to save reward.')
  } finally {
    savingRewardItem.value = false
  }
}

async function deleteRewardItem(id) {
  if (!(await askConfirm('Delete this reward? This cannot be undone.'))) return
  try {
    await api.delete(`/admin/reward-items/${id}`)
    // Deleting the last item on a page beyond page 1 would otherwise leave an
    // empty grid with no obvious way back — step back a page in that case.
    if (rewardItems.value.length === 1 && rewardItemsPage.value > 1) rewardItemsPage.value -= 1
    await fetchTabData('rewards')
  } catch { showToast('Failed to delete reward.', 'error') }
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
  // See UserSettingsView.vue's handleLogout() for why these are captured
  // before logout() clears the auth store.
  const name = auth.user?.name
  await auth.logout()
  router.push({ path: '/thank-you', query: { redirect: '/', name, theme: theme.value } })
}

// ── Lifecycle ──
function updateClock() {
  const now = new Date()
  nowTick.value = now
  liveTime.value = now.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
    + ', ' + fmtTime(now, true)
}

onMounted(async () => {
  updateClock()
  clockTimer = setInterval(updateClock, 1000)
  window.addEventListener('resize', handleResize)
  // Load all tabs in parallel so switching is instant and machines are available immediately
  const preloadTabs = ['dashboard', 'machines', 'users', 'sessions']
  await Promise.allSettled([
    ...preloadTabs.map(tab => fetchTabData(tab, true)),
    // A refresh can land on a tab outside that set (restored from the URL).
    ...(preloadTabs.includes(activeTab.value) ? [] : [fetchTabData(activeTab.value, true)]),
    fetchRewardConfig(),
    fetchCashRedeemSettings(),
    fetchCompactorSettings(),
    fetchChartData(),
  ])
  refreshTimer = setInterval(() => {
    fetchTabData(activeTab.value)
    if (activeTab.value === 'dashboard') {
      fetchChartData()
      // the status card + Machine Alerts read the machine list's heartbeats
      fetchTabData('machines')
    }
  }, 30000)
})

onUnmounted(() => {
  if (refreshTimer) clearInterval(refreshTimer)
  if (clockTimer) clearInterval(clockTimer)
  if (detectionUndoTimer) clearTimeout(detectionUndoTimer)
  window.removeEventListener('resize', handleResize)
  releaseThumbnails()
  stopMaintCamera()
  setRewardImagePreview(null)
})
</script>

<style scoped>
.admin-page {
  display: flex;
  height: 100vh;
  height: 100dvh;
  min-height: 0;
  overflow: hidden; /* the shell itself never scrolls — see .admin-scroll-area */
  background: var(--bg-primary);
}

/* ── Sidebar ── */
.sidebar {
  position: relative;
  width: 220px;
  height: 100%;
  min-height: 0;
  background: var(--bg-secondary);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  flex-shrink: 0;
}
.sidebar.collapsed { width: 60px; }

/* Title/labels/footer fade together with the width transition (both
   directions — collapsing used to remove them instantly via v-if while
   expanding animated in, a jarring asymmetry) rather than being
   mounted/unmounted; each is individually clipped so oversized content
   never visually spills past the collapsed 60px rail. */
.sidebar-title, .nav-label, .sidebar-footer {
  opacity: 1;
  transition: opacity 0.2s ease;
}
.sidebar.collapsed .sidebar-title,
.sidebar.collapsed .nav-label,
.sidebar.collapsed .sidebar-footer {
  opacity: 0;
  pointer-events: none;
}

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 12px;
  border-bottom: 1px solid var(--border);
}
.sidebar-logo { font-size: 20px; flex-shrink: 0; color: var(--accent-green); }
.sidebar-title { font-size: 15px; font-weight: 700; color: var(--text-primary); flex: 1; white-space: nowrap; overflow: hidden; }

/* Floating edge toggle — always the same fixed spot on the sidebar's right
   border, regardless of collapsed state (previously lived inside
   .sidebar-header and got squeezed among the icon-only nav once collapsed). */
.collapse-btn {
  position: absolute;
  top: 20px; right: -12px;
  width: 24px; height: 24px;
  display: flex; align-items: center; justify-content: center;
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 50%;
  color: var(--text-muted);
  cursor: pointer; font-size: 12px;
  z-index: 10;
  transition: background 0.2s, color 0.2s;
}
.collapse-btn:hover { background: var(--bg-hover); color: var(--text-primary); }

.sidebar-nav {
  flex: 1;
  min-height: 0;
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  justify-content: flex-start;
  overflow-y: auto;
  overscroll-behavior: contain;
}
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

/* min-width on the two inner blocks below keeps them at their natural
   expanded-width layout even while the 60px-collapsed .sidebar clips them
   via this overflow:hidden — without it, the text inside would reflow
   (wrap) at the narrower width, changing .sidebar-footer's own height and
   making it visibly shift position (opacity alone doesn't stop that). */
.sidebar-footer {
  flex-shrink: 0;
  padding: 12px;
  padding-bottom: max(12px, env(safe-area-inset-bottom));
  border-top: 1px solid var(--border);
  overflow: hidden;
  background: var(--bg-secondary);
}
.admin-info { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; min-width: 190px; }
.admin-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--grad-header); color: white;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 14px; flex-shrink: 0;
}
.admin-name { font-size: 13px; font-weight: 600; color: var(--text-primary); white-space: nowrap; }
.admin-role { font-size: 11px; color: var(--text-muted); white-space: nowrap; }
.sidebar-actions { display: flex; flex-direction: column; gap: 6px; min-width: 190px; }
.ctrl-btn {
  background: var(--bg-card); border: 1px solid var(--border);
  color: var(--text-secondary); padding: 6px 10px;
  border-radius: 6px; cursor: pointer; font-size: 12px;
  text-decoration: none; text-align: center;
  white-space: nowrap;
}
.logout-btn-sm {
  background: none; border: 1px solid var(--accent-red);
  color: var(--accent-red); padding: 6px 10px;
  border-radius: 6px; cursor: pointer; font-size: 12px;
  white-space: nowrap;
}

/* ── Main ── */
/* App-shell layout: .admin-main itself never scrolls — it just stacks the
   topbar (fixed size) above .admin-scroll-area (the one scrollable region),
   so the sidebar and topbar stay in place while only content moves. */
.admin-main { flex: 1; height: 100%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
.admin-scroll-area { flex: 1; min-height: 0; overflow-y: auto; overscroll-behavior: contain; }

.admin-topbar {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 24px; background: var(--bg-secondary);
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
  position: sticky;
  top: 0;
  z-index: 50;
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
.stat-icon { font-size: 24px; color: var(--accent-blue); }
.stat-value { font-size: 22px; font-weight: 800; color: var(--text-primary); }
.stat-label { font-size: 12px; color: var(--text-muted); }

/* Machine Status card */
.machine-status-card { align-items: flex-start; }
.ms-icon { font-size: 26px; margin-bottom: 6px; color: var(--accent-blue); }
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
/* stroke-width overrides the template's stroke-width="1.5" attribute (a CSS
   property always wins over a presentation attribute) — at 26px the thin
   stroke read as faint/small next to the 36px count below it; bumping size
   alone without thickening the stroke would leave it looking just as frail. */
.ov-icon { width: 36px; height: 36px; stroke-width: 2; margin-bottom: 10px; color: var(--text-secondary); }
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
.ctrl-card:not(:disabled):hover { background: var(--bg-secondary); border-color: var(--accent-blue); }
.ctrl-card:disabled { opacity: 0.55; cursor: not-allowed; }
.ctrl-card-icon { font-size: 32px; padding: 8px; border-radius: 8px; flex-shrink: 0; }
.ctrl-blue   { background: rgba(78, 110, 242, 0.15); }
.ctrl-yellow { background: rgba(245, 158, 11, 0.15); color: var(--accent-yellow); }
.ctrl-purple { background: rgba(168, 85, 247, 0.15); color: var(--accent-purple); }
.ctrl-gray   { background: rgba(107, 114, 128, 0.15); color: var(--text-secondary); }
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
.tx-intro { margin-bottom: 14px; }
.tx-intro .card-title-bar { margin-bottom: 6px; }
.tx-help { margin: 0; font-size: 13px; line-height: 1.5; color: var(--text-secondary); max-width: 760px; }
.tx-help-ok { color: var(--accent-green); }
.tx-help-rej { color: var(--accent-red); }

.tx-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
.tx-sum-card {
  display: flex; flex-direction: column; gap: 2px; padding: 12px 14px;
  border: 1px solid var(--border); border-radius: 8px; background: var(--bg-hover);
}
.tx-sum-value { font-size: 20px; font-weight: 800; color: var(--text-primary); font-variant-numeric: tabular-nums; }
.tx-sum-label { font-size: 12px; color: var(--text-muted); }
.tx-sum-ok { border-color: color-mix(in srgb, var(--accent-green) 45%, var(--border)); }
.tx-sum-ok .tx-sum-value { color: var(--accent-green); }
.tx-sum-rej { border-color: color-mix(in srgb, var(--accent-red) 45%, var(--border)); }
.tx-sum-rej .tx-sum-value { color: var(--accent-red); }
.tx-sum-note { margin: 6px 0 14px; font-size: 11px; color: var(--text-muted); }

.tx-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 10px; margin-bottom: 12px; }
.tx-field { display: flex; flex-direction: column; min-width: 0; }
.tx-field > span { margin-bottom: 5px; color: var(--text-muted); font-size: 11px; font-weight: 600; }
.tx-field .filter-select, .tx-field .search-input { min-height: 38px; box-sizing: border-box; }
.tx-field-search { flex: 1 1 240px; }
.tx-field-search .search-input { width: 100%; }
.tx-field.date-field { flex: 0 0 150px; }
.tx-segment { display: inline-flex; border: 1px solid var(--border); border-radius: 7px; overflow: hidden; min-height: 38px; }
.tx-seg-btn {
  padding: 0 14px; border: 0; background: var(--bg-hover); color: var(--text-secondary);
  font-size: 13px; font-weight: 600; cursor: pointer;
}
.tx-seg-btn + .tx-seg-btn { border-left: 1px solid var(--border); }
.tx-seg-btn.active { background: var(--accent-blue); color: #fff; }
.tx-seg-btn:focus-visible { outline: 2px solid var(--accent-blue); outline-offset: -2px; }
.tx-clear { min-height: 38px; }
.tx-export { margin-left: auto; }
.export-btn { white-space: nowrap; }
.export-btn:disabled { opacity: .6; cursor: wait; }

.tx-table td { vertical-align: top; }
.tx-table .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
.tx-main { font-weight: 600; color: var(--text-primary); white-space: nowrap; }
.tx-sub { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.tx-reason { white-space: normal; max-width: 240px; }
.tx-row-rej td { background: color-mix(in srgb, var(--accent-red) 5%, transparent); }
.tx-badge {
  display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 999px;
  font-size: 12px; font-weight: 700; white-space: nowrap;
}
.tx-badge svg { width: 14px; height: 14px; }
.tx-badge-ok { color: var(--accent-green); background: color-mix(in srgb, var(--accent-green) 14%, transparent); }
.tx-badge-rej { color: var(--accent-red); background: color-mix(in srgb, var(--accent-red) 14%, transparent); }
.tx-conf { font-weight: 700; font-variant-numeric: tabular-nums; }
.tx-conf::before { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; background: currentColor; vertical-align: 1px; }
.tx-conf-high { color: var(--accent-green); }
.tx-conf-mid { color: var(--accent-yellow, #d97706); }
.tx-conf-low { color: var(--accent-red); }
.tx-th-hint {
  display: inline-flex; align-items: center; justify-content: center; width: 14px; height: 14px;
  margin-left: 4px; border-radius: 50%; border: 1px solid currentColor; font-size: 9px; cursor: help;
}
.tx-link-btn { margin-left: 6px; border: 0; background: none; color: var(--accent-blue); font-weight: 600; cursor: pointer; text-decoration: underline; }

@media (max-width: 640px) {
  .tx-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .tx-field.date-field { flex: 1 1 140px; }
}

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
.material-icon-inline { width: 1em; height: 1em; vertical-align: -0.125em; flex-shrink: 0; }

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
.alert-icon { font-size: 18px; color: var(--accent-red); }
.alert-detail { font-size: 12px; color: var(--text-muted); }
.alert-item { flex-wrap: wrap; }
.alert-actions { margin-left: auto; display: flex; gap: 6px; flex-wrap: wrap; }
.suspend-btn { border-color: var(--accent-yellow, #f59e0b) !important; color: var(--accent-yellow, #b45309) !important; }
.reactivate-btn { border-color: var(--accent-green) !important; color: var(--accent-green) !important; }
.suspended-badge { margin-left: 6px; background: rgba(239, 68, 68, 0.12); color: var(--accent-red); padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
.ms-nodata { color: var(--text-muted); }
.machine-heartbeat { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; font-size: 12px; color: var(--text-muted); margin: -6px 0 12px; }
.hb-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--text-muted); flex-shrink: 0; }
.hb-online { background: var(--accent-green); }
.hb-offline { background: var(--accent-red); }
.fault-badge { background: var(--accent-red); color: #fff; padding: 1px 6px; border-radius: 4px; font-size: 11px; font-weight: 600; }
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
.data-table th.sortable { cursor: pointer; user-select: none; }
.data-table th.sortable:hover { color: var(--text-primary); }
.data-table td {
  padding: 10px 12px; border-bottom: 1px solid var(--border);
  color: var(--text-primary); vertical-align: middle;
}
.data-table tr:hover td { background: var(--bg-hover); }
.verified-icon { font-size: 16px; vertical-align: -3px; }
.verified-yes { color: var(--accent-green); }
.verified-no  { color: var(--accent-red); }
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
.machine-actions { display: flex; flex-direction: column; gap: 10px; }
.machine-actions-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
.action-btn.machine-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 6px;
  min-height: 40px; margin: 0; padding: 6px 10px; border-radius: 6px;
  font-size: 13px; font-weight: 600; white-space: nowrap;
}
.action-btn.machine-btn svg { width: 16px; height: 16px; flex-shrink: 0; }
.action-btn.machine-btn:hover { background: var(--bg-card); }
/* Delete: separated and quieter until hovered, so it isn't the loudest button. */
.action-btn.machine-delete {
  align-self: flex-end; border-color: transparent; background: transparent;
  color: var(--accent-red); position: relative;
}
.action-btn.machine-delete:hover { border-color: var(--accent-red); background: color-mix(in srgb, var(--accent-red) 8%, transparent); }
.machine-actions-grid { padding-bottom: 10px; border-bottom: 1px solid var(--border); }

/* ── Rewards (own names — visually identical to Machines above by design,
   but kept separate so a future Machines-only restyle can't silently
   change the Rewards tab too) ── */
.reward-admin-grid {
  display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 14px;
}
.reward-admin-card {
  background: var(--bg-hover); border: 1px solid var(--border);
  border-radius: var(--radius); padding: 14px;
}
.reward-admin-header {
  display: flex; align-items: flex-start;
  justify-content: space-between; margin-bottom: 6px;
}
.reward-admin-badge {
  display: inline-block; background: var(--bg-card);
  padding: 2px 8px; border-radius: 4px;
  font-size: 11px; font-family: monospace;
  color: var(--text-muted); margin-left: 8px;
}
.reward-admin-image {
  display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover;
  border-radius: 8px; margin-bottom: 8px; background: var(--bg-card);
}
.reward-admin-sub { font-size: 12px; color: var(--text-muted); margin-bottom: 12px; }
.reward-admin-actions { display: flex; gap: 6px; flex-wrap: wrap; }

.empty-machines {
  grid-column: 1 / -1; text-align: center;
  color: var(--text-muted); padding: 40px;
}
.empty-machines p { margin-bottom: 14px; }
.empty-rewards {
  grid-column: 1 / -1; text-align: center;
  color: var(--text-muted); padding: 40px;
}
.empty-rewards p { margin-bottom: 14px; }

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
.status-expired    { background: rgba(107, 114, 128, 0.15); color: var(--text-muted); }
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
.user-actions { display: flex; align-items: center; gap: 8px; }
.user-actions .action-btn {
  min-height: 36px; margin-right: 0;
  display: inline-flex; align-items: center; justify-content: center; gap: 6px;
}
.user-action-icon { width: 14px; height: 14px; flex-shrink: 0; }
.edit-btn  { border-color: var(--accent-blue);   color: var(--accent-blue);   }
.del-btn   { border-color: var(--accent-red);    color: var(--accent-red);    }
.maint-btn { border-color: var(--accent-yellow); color: var(--accent-yellow); }
.action-btn:disabled { opacity: 0.5; cursor: not-allowed; }
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
  width: 100%;
  height: 100vh;
  height: 100dvh;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  z-index: 300;
  padding: max(24px, env(safe-area-inset-top)) 20px max(24px, env(safe-area-inset-bottom));
  overflow-y: auto;
  overscroll-behavior: contain;
}
.modal {
  background: var(--bg-card); border: 1px solid var(--border);
  border-radius: 14px; padding: 28px; width: 440px;
  max-width: 100%;
  /* Auto margins center short dialogs. When a dialog is taller than the
     viewport, the vertical auto margins collapse and it starts at the safe
     top padding instead, leaving the overlay itself free to scroll. */
  margin: auto;
  position: relative;
  overflow: visible;
  box-sizing: border-box;
  box-shadow: 0 18px 48px rgba(0,0,0,0.28);
}
.reward-modal { width: 620px; }
.reward-crop-modal { width: 560px; }
.reward-image-preview { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
.reward-image-preview img {
  width: 160px; aspect-ratio: 4 / 3; object-fit: cover;
  border-radius: 8px; border: 1px solid var(--border); background: var(--bg-hover);
}
.reward-image-preview .action-btn { display: inline-flex; align-items: center; gap: 6px; min-height: 34px; padding: 6px 12px; }
.modal h3 { padding-right: 40px; font-size: 17px; font-weight: 700; color: var(--text-primary); margin-bottom: 20px; }
.modal-close-btn {
  position: absolute;
  top: 18px;
  right: 18px;
  width: 36px;
  height: 36px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--bg-hover);
  color: var(--text-muted);
  cursor: pointer;
  font-size: 17px;
  transition: border-color .2s, color .2s, background .2s;
}
.modal-close-btn:not(:disabled):hover {
  border-color: var(--accent-red);
  color: var(--accent-red);
  background: rgba(239,68,68,.08);
}
.modal-close-btn:disabled { opacity: .5; cursor: not-allowed; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 12px; color: var(--text-muted); margin-bottom: 7px; }
.label-hint { font-size: 10px; font-weight: 500; color: var(--text-muted); margin-left: 4px; }
.image-dropzone {
  min-height: 104px; padding: 16px; box-sizing: border-box;
  display: flex !important; flex-direction: column; align-items: center; justify-content: center; gap: 5px;
  text-align: center; cursor: pointer;
  background: var(--bg-hover); border: 1.5px dashed var(--border); border-radius: 9px;
  transition: border-color .2s, background .2s;
}
.image-dropzone:hover, .image-dropzone.dragging {
  border-color: var(--accent-blue); background: rgba(78,110,242,.08);
}
.image-dropzone strong { color: var(--text-primary); font-size: 12px; font-weight: 600; word-break: break-word; }
.image-dropzone span { color: var(--text-muted); font-size: 10px; }
.upload-icon { width: 24px; height: 24px; color: var(--accent-blue); }
.file-input-hidden {
  position: absolute; width: 1px !important; height: 1px; padding: 0 !important;
  margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0 !important;
}
.form-group input,
.form-group select {
  width: 100%; min-height: 44px; padding: 10px 13px; box-sizing: border-box;
  background: var(--bg-hover); border: 1px solid var(--border);
  border-radius: 8px; color: var(--text-primary); font-size: 14px; outline: none;
}
.input-disabled { opacity: 0.5; cursor: not-allowed; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.compactor-hint { font-size: 12px; color: var(--text-muted); margin: -6px 0 14px; max-width: 640px; }
.form-error { font-size: 12px; color: var(--accent-red); margin: 6px 0 0; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; }
.modal-actions .action-btn { min-height: 38px; padding: 8px 16px; margin-right: 0; }

/* ── Kiosk Maintenance modal (QR camera scan) ── */
.maint-hint { font-size: 13px; color: var(--text-secondary); margin-bottom: 14px; }
.qr-frame {
  width: 100%; height: 220px; margin: 0 auto 10px;
  position: relative; display: flex; align-items: center; justify-content: center;
  background: var(--bg-hover); border-radius: var(--radius); overflow: hidden;
}
.qr-frame .camera-feed { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.qr-frame .hidden-canvas { display: none; }
.qr-frame .qr-placeholder svg { width: 40px; height: 40px; color: var(--accent-blue); }
.qr-frame .scan-line {
  position: absolute; left: 12px; right: 12px; height: 2px;
  background: var(--accent-blue); opacity: 0.8; box-shadow: 0 0 8px var(--accent-blue);
  animation: maint-scan 2s ease-in-out infinite;
}
@keyframes maint-scan { 0%, 100% { top: 16px; } 50% { top: calc(100% - 16px); } }
.camera-btn {
  width: 100%; padding: 10px; background: transparent;
  border: 1.5px solid var(--accent-blue); border-radius: var(--radius);
  color: var(--accent-blue); font-size: 13px; font-weight: 600; cursor: pointer;
}
.camera-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Detection Review ── */
.detection-header { margin-bottom: 12px; }
.review-view-tabs {
  display: flex; gap: 8px; margin-bottom: 10px;
  border-bottom: 1px solid var(--border); padding-bottom: 10px;
}
.review-view-btn, .history-filter-btn {
  border: 1px solid var(--border); background: var(--bg-hover);
  color: var(--text-secondary); border-radius: 8px; padding: 8px 16px;
  font-size: 13px; font-weight: 600; cursor: pointer;
}
.review-view-btn.active {
  border-color: var(--accent-blue); background: rgba(78,110,242,0.14);
  color: var(--accent-blue);
}
.history-filters { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.history-filter-btn { padding: 6px 12px; font-size: 12px; }
.history-filter-btn.active {
  border-color: var(--accent-green); background: rgba(34,197,94,0.12);
  color: var(--accent-green);
}
.test-data-note {
  margin: 0 0 10px; padding: 10px 12px;
  border: 1px solid rgba(245,158,11,0.3); border-radius: 8px;
  background: rgba(245,158,11,0.08); color: var(--text-secondary); font-size: 12px;
}
.filter-toolbar {
  display: flex; align-items: flex-end; gap: 10px;
  padding: 12px; margin-bottom: 16px;
  background: var(--bg-hover); border: 1px solid var(--border); border-radius: 10px;
}
.formal-report-toolbar { margin-top: 14px; }
.date-range {
  flex: 1; min-width: 0;
  display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px;
}
.date-field { display: block; min-width: 0; }
.date-field span {
  display: block; margin-bottom: 5px;
  color: var(--text-muted); font-size: 11px; font-weight: 600;
}
.date-field input {
  width: 100%; min-width: 0; min-height: 38px; box-sizing: border-box;
  padding: 8px 10px; background: var(--bg-card);
  border: 1px solid var(--border); border-radius: 7px;
  color: var(--text-primary); font-size: 12px; outline: none;
}
.date-field input:focus { border-color: var(--accent-blue); }
.picker-field, .picker-field input { cursor: pointer; }
.detection-date-preset { flex: 0 0 190px; }
.detection-date-preset .filter-select {
  width: 100%; min-height: 38px; margin: 0;
}
.detection-custom-range { flex: 1; }
.filter-actions { display: flex; gap: 8px; }
.filter-action {
  min-height: 38px; margin-right: 0; padding: 8px 12px;
  display: inline-flex; align-items: center; justify-content: center; gap: 7px;
}
/* overflow-x: clip keeps a swiped-out card from adding a horizontal scrollbar. */
.detection-grid { position: relative; display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; overflow-x: clip; align-items: start; }
.detection-card {
  position: relative;
  background: var(--bg-card); border: 1px solid var(--border);
  color: var(--text-primary); border-radius: 10px;
  overflow: hidden; display: flex; flex-direction: column;
}
/* Swipe feedback (directives/swipe.js sets data-swipe-dir + --swipe-progress). */
.swipe-hint {
  position: absolute; inset: 0; z-index: 1;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  color: #fff; font-size: 18px; font-weight: 700;
  opacity: 0; pointer-events: none;
}
.swipe-hint svg { width: 28px; height: 28px; }
.swipe-hint-correct { background: rgba(34,197,94,0.85); }
.swipe-hint-incorrect { background: rgba(239,68,68,0.85); }
.detection-card[data-swipe-dir="right"] .swipe-hint-correct,
.detection-card[data-swipe-dir="left"] .swipe-hint-incorrect { opacity: var(--swipe-progress, 0); }
/* The whole photo, at its own aspect ratio (no cropping). */
.detection-thumb { display: block; width: 100%; height: auto; background: var(--bg-hover); }
.detection-thumb.tappable { cursor: zoom-in; }
.detection-thumb.placeholder { height: 160px; display: flex; align-items: center; justify-content: center; font-size: 32px; color: var(--text-muted); }
.detection-meta { padding: 10px 12px; }
.detection-badges { display: flex; gap: 6px; margin-bottom: 6px; }
.detection-result { font-size: 13px; font-weight: 600; }
.detection-time { color: var(--text-muted); font-size: 12px; }
.review-history-meta {
  margin-top: 9px; padding-top: 9px; border-top: 1px solid var(--border);
  display: grid; gap: 4px;
}
.review-history-meta p { margin: 0; color: var(--text-secondary); font-size: 11px; }
.review-history-meta span { color: var(--text-muted); }
.badge-reviewed-correct { background: rgba(34,197,94,0.14); color: var(--accent-green); }
.badge-reviewed-incorrect { background: rgba(239,68,68,0.14); color: var(--accent-red); }
.detection-actions { display: flex; gap: 8px; padding: 0 12px 12px; }
.review-btn {
  flex: 1; padding: 6px 8px; border-radius: 6px;
  border: 1px solid var(--border); background: var(--bg-hover);
  color: var(--text-secondary); cursor: pointer; font-size: 12px;
}
.review-btn.active { background: var(--accent-green); border-color: var(--accent-green); color: #0e1220; }
.review-btn.reject.active { background: var(--accent-red); border-color: var(--accent-red); color: #fff; }
.review-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.detection-disabled-note {
  font-size: 11px; color: var(--text-muted); text-align: center;
  padding: 0 12px 10px; margin-top: -2px;
}
.detection-card-move,
.detection-card-enter-active,
.detection-card-leave-active { transition: opacity .22s ease, transform .22s ease; }
.detection-card-enter-from,
.detection-card-leave-to { opacity: 0; transform: scale(.96); }
.detection-card-leave-active { position: absolute; }

.detection-review-modal { width: 560px; }
.review-modal-summary {
  display: flex; flex-direction: column; gap: 10px;
  padding: 12px; margin-bottom: 18px;
  background: var(--bg-hover); border: 1px solid var(--border); border-radius: 10px;
}
/* Whole photo; capped so the material buttons stay on screen. */
.review-modal-image {
  display: block; width: 100%; height: auto; max-height: 50vh; object-fit: contain;
  background: var(--bg-card); border-radius: 8px;
}
.review-modal-prediction { text-align: center; }
.review-modal-image.placeholder { height: 110px;
  display: flex; align-items: center; justify-content: center;
  padding: 34px; color: var(--text-muted);
}
.review-modal-summary span { display: block; color: var(--text-muted); font-size: 12px; margin-bottom: 5px; }
.review-modal-summary strong { color: var(--text-primary); font-size: 15px; text-transform: capitalize; }
.detection-viewer {
  position: relative; margin: auto; width: min(960px, 100%);
  background: var(--bg-card); border: 1px solid var(--border); border-radius: 14px;
  padding: 48px 16px 16px; box-sizing: border-box;
  box-shadow: 0 18px 48px rgba(0,0,0,0.35);
}
.detection-viewer-image {
  display: block; width: 100%; height: auto; max-height: 72vh; object-fit: contain;
  background: #000; border-radius: 10px;
}
.detection-viewer-caption { text-align: center; margin: 12px 0 4px; }
.detection-viewer-caption span { display: block; color: var(--text-muted); font-size: 12px; }
.detection-viewer-caption strong { display: block; color: var(--text-primary); font-size: 18px; text-transform: capitalize; }
.detection-viewer-caption small { color: var(--text-muted); font-size: 12px; }
.detection-viewer-actions { padding: 12px 0 0; }
.detection-viewer-actions .review-btn { padding: 12px 8px; font-size: 14px; }
.actual-material-prompt { color: var(--text-primary); font-size: 13px; font-weight: 600; margin: 0 0 10px; }
.actual-material-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
.actual-material-btn {
  min-height: 48px; display: flex; flex-direction: column; align-items: center; justify-content: center;
  border: 1px solid var(--border); border-radius: 9px; background: var(--bg-hover);
  color: var(--text-primary); font-size: 13px; font-weight: 600; cursor: pointer;
}
.actual-material-btn:hover:not(:disabled) { border-color: var(--accent-blue); }
.actual-material-btn:disabled { opacity: .45; cursor: not-allowed; }
.actual-material-btn small { color: var(--text-muted); font-size: 9px; font-weight: 400; margin-top: 2px; }

/* .badge-warning marks a *fabricated* (mock) AI result, so it has to read as a
   warning at a glance — those rows can't be honestly scored. */
.badge {
  display: inline-block; padding: 2px 8px; border-radius: 20px;
  font-size: 11px; font-weight: 600;
  background: var(--bg-hover); border: 1px solid var(--border);
  color: var(--text-secondary);
}
.badge-warning {
  /* Solid fill rather than the tinted .status-maintenance treatment: amber text
     on the light theme's white card is barely readable, and this badge has to
     be impossible to miss. */
  background: var(--accent-yellow);
  border-color: var(--accent-yellow);
  color: #1a1200; font-weight: 700; letter-spacing: 0.3px;
}

/* ── Confirm modal ── */
.confirm-modal { width: 340px; }
.confirm-message { padding-right: 40px; font-size: 14px; color: var(--text-primary); line-height: 1.5; margin: 0; }
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
.toast-icon { font-size: 14px; flex-shrink: 0; color: var(--accent-green); }
.toast.error .toast-icon { color: var(--accent-red); }
.detection-undo { gap: 10px; }
.undo-review-btn {
  margin-left: 8px; padding: 4px 9px; border: 1px solid var(--accent-blue);
  border-radius: 5px; background: transparent; color: var(--accent-blue);
  font-size: 12px; font-weight: 700; cursor: pointer;
}
.undo-review-btn:disabled { opacity: .55; cursor: wait; }
.toast-fade-enter-active, .toast-fade-leave-active { transition: opacity 0.2s, transform 0.2s; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translate(-50%, 8px); }
/* Undo toast: slides down/out, then the next review's slides up/in (out-in). */
.undo-swap-enter-active, .undo-swap-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.undo-swap-enter-from, .undo-swap-leave-to { opacity: 0; transform: translate(-50%, 16px); }
@media (prefers-reduced-motion: reduce) {
  .toast-fade-enter-active, .toast-fade-leave-active { transition: none; }
  .undo-swap-enter-active, .undo-swap-leave-active { transition: none; }
  .detection-card-move, .detection-card-enter-active, .detection-card-leave-active { transition: none; }
  .sidebar, .sidebar-title, .nav-label, .sidebar-footer { transition: none; }
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
.pts-red { color: var(--accent-red); font-weight: 600; }

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
  /* Collapse-to-icon-rail is a desktop affordance — the mobile sidebar is
     always a full-width drawer (width: 220px !important below), so
     "collapsing" it here only hid the header title with no other visible
     effect. Hide the control itself rather than leave a button that does
     something confusing. */
  .collapse-btn { display: none; }
  .sidebar {
    position: fixed; left: 0; top: 0; bottom: auto; z-index: 200;
    height: 100vh;
    height: 100dvh;
    max-height: 100dvh;
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
  .machines-grid    { grid-template-columns: 1fr; }

  .admin-topbar     { flex-wrap: wrap; gap: 8px; }
  .topbar-right     { flex-wrap: wrap; }
  .table-wrap       { overflow-x: auto; }

  /* Some tabs pair a title with search/filter controls via space-between.
     Stack those headers on mobile so both sides keep their full width. */
  .card-header      { flex-direction: column; align-items: flex-start; gap: 10px; }

  /* Header tools on a phone: the search box gets a full row, the Export /
     Add buttons share the next one at equal width, all 44px tall to tap. */
  .card-header > .topbar-right { width: 100%; gap: 8px; }
  .card-header > .topbar-right > .search-input,
  .card-header > .topbar-right > .filter-select { flex: 1 1 100%; width: 100%; min-height: 44px; box-sizing: border-box; }
  .card-header > .topbar-right > button { flex: 1 1 0; }
  .card-header > .export-btn { width: 100%; }
  .export-btn, .card-header .add-btn { min-height: 44px; font-size: 13px; }
  /* Transactions: Export sits alone under the filters so it isn't read as a filter field. */
  .tx-filters .tx-export { flex: 1 1 100%; margin-left: 0; }
  .action-btn.machine-btn { min-height: 44px; }
  .action-btn.machine-delete { align-self: stretch; border-color: color-mix(in srgb, var(--accent-red) 35%, var(--border)); }
}

/* Small mobile: ≤ 480px */
@media (max-width: 480px) {
  .stats-grid       { grid-template-columns: repeat(2, 1fr); }
  .overview-cards   { grid-template-columns: 1fr 1fr; }
  .ov-count         { font-size: 26px; }
  .controls-grid    { grid-template-columns: 1fr; }
  .reward-materials { grid-template-columns: 1fr; }
  .section-card     { padding: 12px; }
  .admin-topbar     { padding: 10px 12px; }
  .tab-content      { padding: 12px; }
  .filter-toolbar { flex-direction: column; align-items: stretch; gap: 10px; padding: 10px; }
  .date-range { gap: 8px; }
  .detection-filter-toolbar { align-items: stretch; }
  .detection-date-preset { flex: 1 1 100%; }
  .filter-actions { width: 100%; }
  .filter-action { flex: 1; }
  .review-view-tabs, .history-filters { width: 100%; }
  .review-view-btn { flex: 1; }
  .user-actions     { flex-direction: column; align-items: stretch; gap: 8px; }
  .user-actions .action-btn { min-width: 82px; min-height: 40px; padding: 8px 12px; }
  .modal-overlay {
    padding: max(20px, env(safe-area-inset-top)) 16px max(20px, env(safe-area-inset-bottom));
  }
  .modal { padding: 20px; }
  .modal h3 { margin-bottom: 18px; }
}

@media (max-width: 360px) {
  .modal-overlay {
    padding: max(16px, env(safe-area-inset-top)) 12px max(16px, env(safe-area-inset-bottom));
  }
  .modal { padding: 18px; }
}
</style>
