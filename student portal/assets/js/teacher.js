// Teacher Dashboard JavaScript

// Modal functions (inherited from admin.js)
function openModal(modalId) {
  document.getElementById(modalId).style.display = "block"
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none"
}

// Close modal when clicking outside
window.onclick = (event) => {
  if (event.target.classList.contains("modal")) {
    event.target.style.display = "none"
  }
}

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

  // Grade calculation helper
  const gradeInput = document.getElementById("grade")
  const maxGradeInput = document.getElementById("max_grade")

  if (gradeInput && maxGradeInput) {
    function updatePercentage() {
      const grade = Number.parseFloat(gradeInput.value) || 0
      const maxGrade = Number.parseFloat(maxGradeInput.value) || 100
      const percentage = (grade / maxGrade) * 100

      // Create or update percentage display
      let percentageDisplay = document.getElementById("percentage-display")
      if (!percentageDisplay) {
        percentageDisplay = document.createElement("div")
        percentageDisplay.id = "percentage-display"
        percentageDisplay.style.cssText = `
                    margin-top: 10px;
                    padding: 8px;
                    border-radius: 4px;
                    font-weight: 600;
                    text-align: center;
                `
        maxGradeInput.parentNode.appendChild(percentageDisplay)
      }

      if (grade > 0 && maxGrade > 0) {
        percentageDisplay.textContent = `${percentage.toFixed(1)}%`

        // Color code based on percentage
        if (percentage >= 90) {
          percentageDisplay.style.background = "#27ae60"
          percentageDisplay.style.color = "white"
        } else if (percentage >= 80) {
          percentageDisplay.style.background = "#2ecc71"
          percentageDisplay.style.color = "white"
        } else if (percentage >= 70) {
          percentageDisplay.style.background = "#f39c12"
          percentageDisplay.style.color = "white"
        } else if (percentage >= 60) {
          percentageDisplay.style.background = "#e67e22"
          percentageDisplay.style.color = "white"
        } else {
          percentageDisplay.style.background = "#e74c3c"
          percentageDisplay.style.color = "white"
        }
      } else {
        percentageDisplay.textContent = ""
        percentageDisplay.style.background = "transparent"
      }
    }

    gradeInput.addEventListener("input", updatePercentage)
    maxGradeInput.addEventListener("input", updatePercentage)
  }

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

  // Form validation
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const requiredFields = form.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          field.style.borderColor = "#e74c3c"
          isValid = false
        } else {
          field.style.borderColor = "#ddd"
        }
      })

      // Specific validation for grade forms
      if (form.querySelector('input[name="grade"]')) {
        const grade = Number.parseFloat(form.querySelector('input[name="grade"]').value)
        const maxGrade = Number.parseFloat(form.querySelector('input[name="max_grade"]').value)

        if (grade > maxGrade) {
          alert("Grade cannot be higher than maximum grade.")
          e.preventDefault()
          return
        }

        if (grade < 0 || maxGrade < 0) {
          alert("Grades cannot be negative.")
          e.preventDefault()
          return
        }
      }

      if (!isValid) {
        e.preventDefault()
        alert("Please fill in all required fields.")
      }
    })
  })

  // Quick grade entry shortcuts
  const assignmentNameInput = document.getElementById("assignment_name")
  if (assignmentNameInput) {
    const commonAssignments = [
      "Quiz 1",
      "Quiz 2",
      "Midterm Exam",
      "Final Exam",
      "Homework 1",
      "Homework 2",
      "Project",
      "Presentation",
      "Lab Report",
      "Essay",
    ]

    // Create datalist for autocomplete
    const datalist = document.createElement("datalist")
    datalist.id = "assignment-suggestions"
    commonAssignments.forEach((assignment) => {
      const option = document.createElement("option")
      option.value = assignment
      datalist.appendChild(option)
    })

    assignmentNameInput.setAttribute("list", "assignment-suggestions")
    assignmentNameInput.parentNode.appendChild(datalist)
  }

  // Class statistics updates
  updateClassStatistics()
})

// Update class statistics dynamically
function updateClassStatistics() {
  const statCards = document.querySelectorAll(".stat-card")
  statCards.forEach((card) => {
    card.addEventListener("mouseenter", () => {
      card.style.transform = "translateY(-8px)"
    })
    card.addEventListener("mouseleave", () => {
      card.style.transform = "translateY(-5px)"
    })
  })
}

// Export grades to CSV
function exportGradesToCSV(classId, className) {
  const table = document.querySelector(".data-table")
  if (!table) return

  const csv = []
  const rows = table.querySelectorAll("tr")

  rows.forEach((row) => {
    const cols = row.querySelectorAll("td, th")
    const rowData = Array.from(cols).map((col) => {
      return '"' + col.textContent.trim().replace(/"/g, '""') + '"'
    })
    csv.push(rowData.join(","))
  })

  const csvContent = csv.join("\n")
  const blob = new Blob([csvContent], { type: "text/csv" })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement("a")
  a.href = url
  a.download = `${className}_grades.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

// Bulk grade entry helper
function enableBulkGradeEntry() {
  const modal = document.getElementById("addGradeModal")
  if (!modal) return

  const bulkButton = document.createElement("button")
  bulkButton.textContent = "Bulk Entry Mode"
  bulkButton.className = "btn btn-secondary"
  bulkButton.type = "button"
  bulkButton.onclick = toggleBulkMode

  const modalActions = modal.querySelector(".modal-actions")
  modalActions.insertBefore(bulkButton, modalActions.firstChild)
}

function toggleBulkMode() {
  // Implementation for bulk grade entry
  alert("Bulk grade entry mode - Feature coming soon!")
}

// Grade analytics
function calculateGradeDistribution() {
  const percentageBadges = document.querySelectorAll(".percentage-badge")
  const distribution = { A: 0, B: 0, C: 0, D: 0, F: 0 }

  percentageBadges.forEach((badge) => {
    const percentage = Number.parseFloat(badge.textContent)
    if (percentage >= 90) distribution.A++
    else if (percentage >= 80) distribution.B++
    else if (percentage >= 70) distribution.C++
    else if (percentage >= 60) distribution.D++
    else distribution.F++
  })

  return distribution
}

// Mobile responsiveness helpers
function handleMobileView() {
  if (window.innerWidth <= 768) {
    // Adjust table display for mobile
    const tables = document.querySelectorAll(".data-table")
    tables.forEach((table) => {
      table.style.fontSize = "0.8rem"
    })

    // Stack form elements vertically on mobile
    const formRows = document.querySelectorAll(".form-row")
    formRows.forEach((row) => {
      row.style.flexDirection = "column"
    })
  }
}

window.addEventListener("resize", handleMobileView)
handleMobileView() // Call on load
