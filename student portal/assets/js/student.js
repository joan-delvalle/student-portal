// Student Portal JavaScript

document.addEventListener("DOMContentLoaded", () => {
  // Set active menu item
  const currentPage = window.location.pathname.split("/").pop()
  const menuLinks = document.querySelectorAll(".menu-link")

  menuLinks.forEach((link) => {
    link.classList.remove("active")
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active")
    }
  })

  // Auto-hide alerts
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.remove()
      }, 300)
    }, 5000)
  })

  // Grade performance indicators
  addPerformanceIndicators()

  // Academic progress visualization
  createProgressCharts()

  // GPA calculator
  initializeGPACalculator()

  // Print functionality enhancement
  enhancePrintFunctionality()
})

// Add performance indicators to grades
function addPerformanceIndicators() {
  const percentageBadges = document.querySelectorAll(".percentage-badge")

  percentageBadges.forEach((badge) => {
    const percentage = Number.parseFloat(badge.textContent)
    let indicator = ""
    let className = ""

    if (percentage >= 95) {
      indicator = "Excellent"
      className = "performance-excellent"
    } else if (percentage >= 85) {
      indicator = "Good"
      className = "performance-good"
    } else if (percentage >= 75) {
      indicator = "Average"
      className = "performance-average"
    } else if (percentage >= 65) {
      indicator = "Below Average"
      className = "performance-below"
    } else {
      indicator = "Needs Improvement"
      className = "performance-poor"
    }

    // Add indicator if not already present
    if (!badge.nextElementSibling || !badge.nextElementSibling.classList.contains("performance-indicator")) {
      const indicatorSpan = document.createElement("span")
      indicatorSpan.className = `performance-indicator ${className}`
      indicatorSpan.textContent = indicator
      badge.parentNode.insertBefore(indicatorSpan, badge.nextSibling)
    }
  })
}

// Create progress charts for academic performance
function createProgressCharts() {
  const statsCards = document.querySelectorAll(".stat-card")

  statsCards.forEach((card) => {
    const statContent = card.querySelector(".stat-content")
    const value = statContent.querySelector("h3").textContent

    // Add progress bar for GPA and attendance
    if (statContent.querySelector("p").textContent.includes("GPA")) {
      const gpaValue = Number.parseFloat(value)
      if (!isNaN(gpaValue)) {
        addProgressBar(statContent, (gpaValue / 4.0) * 100, "GPA Progress")
      }
    } else if (statContent.querySelector("p").textContent.includes("Attendance")) {
      const attendanceValue = Number.parseFloat(value.replace("%", ""))
      if (!isNaN(attendanceValue)) {
        addProgressBar(statContent, attendanceValue, "Attendance Rate")
      }
    }
  })
}

// Add progress bar to stat card
function addProgressBar(container, percentage, label) {
  const progressContainer = document.createElement("div")
  progressContainer.className = "progress-chart"

  const progressBar = document.createElement("div")
  progressBar.className = "progress-bar"

  const progressFill = document.createElement("div")
  progressFill.className = "progress-fill"
  progressFill.style.width = `${Math.min(percentage, 100)}%`

  progressBar.appendChild(progressFill)
  progressContainer.appendChild(progressBar)
  container.appendChild(progressContainer)

  // Animate progress bar
  setTimeout(() => {
    progressFill.style.width = `${Math.min(percentage, 100)}%`
  }, 500)
}

// Initialize GPA calculator
function initializeGPACalculator() {
  // Add GPA calculation tooltip to GPA stat card
  const gpaCard = document.querySelector('.stat-card:has(.stat-content p:contains("GPA"))')
  if (gpaCard) {
    gpaCard.title = "GPA is calculated on a 4.0 scale based on your assignment grades"
    gpaCard.style.cursor = "help"
  }
}

// Enhance print functionality
function enhancePrintFunctionality() {
  // Add print styles dynamically
  const printButton = document.querySelector('button[onclick="window.print()"]')
  if (printButton) {
    printButton.addEventListener("click", () => {
      // Add print-specific classes before printing
      document.body.classList.add("printing")

      // Remove after print dialog
      setTimeout(() => {
        document.body.classList.remove("printing")
      }, 1000)
    })
  }
}

// Grade trend analysis
function analyzeGradeTrends() {
  const gradeRows = document.querySelectorAll(".data-table tbody tr")
  const grades = []

  gradeRows.forEach((row) => {
    const percentageCell = row.querySelector(".percentage-badge")
    const dateCell = row.cells[row.cells.length - 2] // Assuming date is second to last

    if (percentageCell && dateCell) {
      const percentage = Number.parseFloat(percentageCell.textContent)
      const date = new Date(dateCell.textContent)

      grades.push({ percentage, date })
    }
  })

  // Sort by date
  grades.sort((a, b) => a.date - b.date)

  // Calculate trend
  if (grades.length >= 2) {
    const recent = grades.slice(-3) // Last 3 grades
    const older = grades.slice(0, -3) // Earlier grades

    const recentAvg = recent.reduce((sum, g) => sum + g.percentage, 0) / recent.length
    const olderAvg = older.length > 0 ? older.reduce((sum, g) => sum + g.percentage, 0) / older.length : recentAvg

    const trend = recentAvg - olderAvg

    // Display trend indicator
    displayTrendIndicator(trend)
  }
}

// Display grade trend indicator
function displayTrendIndicator(trend) {
  const pageHeader = document.querySelector(".page-header")
  if (!pageHeader) return

  const trendIndicator = document.createElement("div")
  trendIndicator.className = "trend-indicator"

  let trendText = ""
  let trendClass = ""

  if (trend > 5) {
    trendText = "📈 Improving Performance"
    trendClass = "trend-up"
  } else if (trend < -5) {
    trendText = "📉 Declining Performance"
    trendClass = "trend-down"
  } else {
    trendText = "📊 Stable Performance"
    trendClass = "trend-stable"
  }

  trendIndicator.innerHTML = `<span class="${trendClass}">${trendText}</span>`
  trendIndicator.style.cssText = `
        margin-top: 10px;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 0.9rem;
        font-weight: 500;
    `

  if (trendClass === "trend-up") {
    trendIndicator.style.background = "#d4edda"
    trendIndicator.style.color = "#155724"
  } else if (trendClass === "trend-down") {
    trendIndicator.style.background = "#f8d7da"
    trendIndicator.style.color = "#721c24"
  } else {
    trendIndicator.style.background = "#d1ecf1"
    trendIndicator.style.color = "#0c5460"
  }

  pageHeader.appendChild(trendIndicator)
}

// Study recommendations based on performance
function generateStudyRecommendations() {
  const percentageBadges = document.querySelectorAll(".percentage-badge")
  const lowPerformanceSubjects = []

  percentageBadges.forEach((badge) => {
    const percentage = Number.parseFloat(badge.textContent)
    if (percentage < 75) {
      const row = badge.closest("tr")
      const subjectCell = row.cells[0] // Assuming subject is first column
      lowPerformanceSubjects.push({
        subject: subjectCell.textContent,
        percentage: percentage,
      })
    }
  })

  if (lowPerformanceSubjects.length > 0) {
    displayStudyRecommendations(lowPerformanceSubjects)
  }
}

// Display study recommendations
function displayStudyRecommendations(subjects) {
  const mainContent = document.querySelector(".main-content")
  if (!mainContent) return

  const recommendationsCard = document.createElement("div")
  recommendationsCard.className = "card study-recommendations"
  recommendationsCard.innerHTML = `
        <div class="card-header">
            <h2 class="card-title">📚 Study Recommendations</h2>
        </div>
        <div class="recommendations-content">
            <p>Based on your current performance, consider focusing on:</p>
            <ul>
                ${subjects
                  .map(
                    (subject) => `
                    <li>
                        <strong>${subject.subject}</strong> - Current: ${subject.percentage}%
                        <span class="recommendation-tip">Consider additional practice and review</span>
                    </li>
                `,
                  )
                  .join("")}
            </ul>
        </div>
    `

  recommendationsCard.style.cssText = `
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        margin-bottom: 20px;
    `

  mainContent.appendChild(recommendationsCard)
}

// Initialize all student portal features
function initializeStudentPortal() {
  analyzeGradeTrends()
  generateStudyRecommendations()

  // Add interactive features to stat cards
  const statCards = document.querySelectorAll(".stat-card")
  statCards.forEach((card) => {
    card.addEventListener("mouseenter", () => {
      card.style.transform = "translateY(-8px)"
      card.style.boxShadow = "0 8px 25px rgba(0,0,0,0.15)"
    })

    card.addEventListener("mouseleave", () => {
      card.style.transform = "translateY(0)"
      card.style.boxShadow = "0 2px 10px rgba(0,0,0,0.1)"
    })
  })
}

// Call initialization after DOM is loaded
setTimeout(initializeStudentPortal, 1000)

// Export transcript functionality
function exportTranscript() {
  const transcriptTable = document.querySelector(".transcript-table")
  if (!transcriptTable) return

  // Create CSV content
  const csv = []
  const rows = transcriptTable.querySelectorAll("tr")

  rows.forEach((row) => {
    if (!row.classList.contains("year-separator")) {
      const cols = row.querySelectorAll("td, th")
      const rowData = Array.from(cols).map((col) => {
        return '"' + col.textContent.trim().replace(/"/g, '""') + '"'
      })
      csv.push(rowData.join(","))
    }
  })

  // Download CSV
  const csvContent = csv.join("\n")
  const blob = new Blob([csvContent], { type: "text/csv" })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement("a")
  a.href = url
  a.download = "academic_transcript.csv"
  a.click()
  window.URL.revokeObjectURL(url)
}

// Mobile responsiveness
function handleMobileView() {
  if (window.innerWidth <= 768) {
    // Adjust table display for mobile
    const tables = document.querySelectorAll(".data-table, .transcript-table")
    tables.forEach((table) => {
      table.style.fontSize = "0.8rem"
    })

    // Stack stat cards vertically on mobile
    const statsGrid = document.querySelector(".stats-grid")
    if (statsGrid) {
      statsGrid.style.gridTemplateColumns = "1fr"
    }
  }
}

window.addEventListener("resize", handleMobileView)
handleMobileView() // Call on load
