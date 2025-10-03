// Admin Dashboard JavaScript

// Modal functions
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

// Sidebar toggle for mobile
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar")
  sidebar.classList.toggle("open")
}

// Add mobile menu button if needed
document.addEventListener("DOMContentLoaded", () => {
  if (window.innerWidth <= 768) {
    const navbar = document.querySelector(".navbar-content")
    const menuButton = document.createElement("button")
    menuButton.innerHTML = "☰"
    menuButton.className = "mobile-menu-btn"
    menuButton.onclick = toggleSidebar
    navbar.insertBefore(menuButton, navbar.firstChild)
  }

  // Set active menu item
  const currentPage = window.location.pathname.split("/").pop()
  const menuLinks = document.querySelectorAll(".menu-link")

  menuLinks.forEach((link) => {
    link.classList.remove("active")
    if (link.getAttribute("href") === currentPage) {
      link.classList.add("active")
    }
  })

  // Auto-hide alerts after 5 seconds
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.remove()
      }, 300)
    }, 5000)
  })

  // Form validation enhancement
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

      if (!isValid) {
        e.preventDefault()
        alert("Please fill in all required fields.")
      }
    })
  })

  // Search functionality enhancement
  const searchInputs = document.querySelectorAll('input[name="search"]')
  searchInputs.forEach((input) => {
    let searchTimeout
    input.addEventListener("input", function () {
      clearTimeout(searchTimeout)
      searchTimeout = setTimeout(() => {
        // Auto-submit search after 500ms of no typing
        if (this.value.length >= 3 || this.value.length === 0) {
          this.form.submit()
        }
      }, 500)
    })
  })
})

// Confirmation dialogs for destructive actions
function confirmAction(message) {
  return confirm(message || "Are you sure you want to perform this action?")
}

// Data table enhancements
function sortTable(columnIndex, tableId = "dataTable") {
  const table = document.getElementById(tableId)
  if (!table) return

  const tbody = table.querySelector("tbody")
  const rows = Array.from(tbody.querySelectorAll("tr"))

  rows.sort((a, b) => {
    const aText = a.cells[columnIndex].textContent.trim()
    const bText = b.cells[columnIndex].textContent.trim()

    // Try to parse as numbers first
    const aNum = Number.parseFloat(aText)
    const bNum = Number.parseFloat(bText)

    if (!isNaN(aNum) && !isNaN(bNum)) {
      return aNum - bNum
    }

    // Fall back to string comparison
    return aText.localeCompare(bText)
  })

  // Re-append sorted rows
  rows.forEach((row) => tbody.appendChild(row))
}

// Export functionality
function exportTableToCSV(tableId, filename = "export.csv") {
  const table = document.getElementById(tableId)
  if (!table) return

  const csv = []
  const rows = table.querySelectorAll("tr")

  rows.forEach((row) => {
    const cols = row.querySelectorAll("td, th")
    const rowData = Array.from(cols).map((col) => {
      // Clean up the text content
      return '"' + col.textContent.trim().replace(/"/g, '""') + '"'
    })
    csv.push(rowData.join(","))
  })

  // Create and download the file
  const csvContent = csv.join("\n")
  const blob = new Blob([csvContent], { type: "text/csv" })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement("a")
  a.href = url
  a.download = filename
  a.click()
  window.URL.revokeObjectURL(url)
}

// Real-time notifications (if needed)
function showNotification(message, type = "info") {
  const notification = document.createElement("div")
  notification.className = `notification notification-${type}`
  notification.textContent = message

  // Style the notification
  notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        font-weight: 500;
        z-index: 1001;
        animation: slideIn 0.3s ease;
    `

  // Set background color based on type
  const colors = {
    success: "#27ae60",
    error: "#e74c3c",
    warning: "#f39c12",
    info: "#3498db",
  }
  notification.style.backgroundColor = colors[type] || colors.info

  document.body.appendChild(notification)

  // Auto-remove after 5 seconds
  setTimeout(() => {
    notification.style.animation = "slideOut 0.3s ease"
    setTimeout(() => {
      notification.remove()
    }, 300)
  }, 5000)
}

// Add CSS for notifications
const notificationStyles = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`

const styleSheet = document.createElement("style")
styleSheet.textContent = notificationStyles
document.head.appendChild(styleSheet)
