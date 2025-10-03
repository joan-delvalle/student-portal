import Link from "next/link"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { GraduationCap, Users, BookOpen, Shield } from "lucide-react"

export default function HomePage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
      <div className="container mx-auto px-4 py-16">
        {/* Header */}
        <div className="text-center mb-16">
          <div className="flex items-center justify-center mb-6">
            <GraduationCap className="h-16 w-16 text-blue-600 mr-4" />
            <h1 className="text-5xl font-bold text-gray-900">EduManage</h1>
          </div>
          <p className="text-xl text-gray-600 max-w-2xl mx-auto">
            Comprehensive School Management System for administrators, teachers, and students
          </p>
        </div>

        {/* Role Selection Cards */}
        <div className="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
          {/* Admin Card */}
          <Card className="hover:shadow-lg transition-shadow duration-300 border-2 hover:border-blue-300">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-red-100 rounded-full w-fit">
                <Shield className="h-8 w-8 text-red-600" />
              </div>
              <CardTitle className="text-2xl font-bold text-gray-900">Administrator</CardTitle>
              <CardDescription className="text-gray-600">Manage users, classes, and system settings</CardDescription>
            </CardHeader>
            <CardContent className="text-center">
              <div className="space-y-3">
                <Button asChild className="w-full bg-red-600 hover:bg-red-700">
                  <Link href="/admin/login">Admin Login</Link>
                </Button>
                <p className="text-sm text-gray-500">Full system access and management</p>
              </div>
            </CardContent>
          </Card>

          {/* Teacher Card */}
          <Card className="hover:shadow-lg transition-shadow duration-300 border-2 hover:border-blue-300">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-green-100 rounded-full w-fit">
                <Users className="h-8 w-8 text-green-600" />
              </div>
              <CardTitle className="text-2xl font-bold text-gray-900">Teacher</CardTitle>
              <CardDescription className="text-gray-600">Manage classes, grades, and student progress</CardDescription>
            </CardHeader>
            <CardContent className="text-center">
              <div className="space-y-3">
                <Button asChild className="w-full bg-green-600 hover:bg-green-700">
                  <Link href="/teacher/login">Teacher Login</Link>
                </Button>
                <p className="text-sm text-gray-500">Class and grade management</p>
              </div>
            </CardContent>
          </Card>

          {/* Student Card */}
          <Card className="hover:shadow-lg transition-shadow duration-300 border-2 hover:border-blue-300">
            <CardHeader className="text-center pb-4">
              <div className="mx-auto mb-4 p-3 bg-blue-100 rounded-full w-fit">
                <BookOpen className="h-8 w-8 text-blue-600" />
              </div>
              <CardTitle className="text-2xl font-bold text-gray-900">Student</CardTitle>
              <CardDescription className="text-gray-600">View grades, classes, and academic progress</CardDescription>
            </CardHeader>
            <CardContent className="text-center">
              <div className="space-y-3">
                <Button asChild className="w-full bg-blue-600 hover:bg-blue-700">
                  <Link href="/student/login">Student Login</Link>
                </Button>
                <Button asChild variant="outline" className="w-full bg-transparent">
                  <Link href="/student/signup">Student Signup</Link>
                </Button>
                <p className="text-sm text-gray-500">Access your academic information</p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Features Section */}
        <div className="mt-20 text-center">
          <h2 className="text-3xl font-bold text-gray-900 mb-8">System Features</h2>
          <div className="grid md:grid-cols-4 gap-6 max-w-4xl mx-auto">
            <div className="p-4">
              <div className="bg-blue-100 rounded-full p-3 w-fit mx-auto mb-3">
                <Users className="h-6 w-6 text-blue-600" />
              </div>
              <h3 className="font-semibold text-gray-900 mb-2">User Management</h3>
              <p className="text-sm text-gray-600">Comprehensive user role management</p>
            </div>
            <div className="p-4">
              <div className="bg-green-100 rounded-full p-3 w-fit mx-auto mb-3">
                <BookOpen className="h-6 w-6 text-green-600" />
              </div>
              <h3 className="font-semibold text-gray-900 mb-2">Grade Tracking</h3>
              <p className="text-sm text-gray-600">Real-time grade and progress monitoring</p>
            </div>
            <div className="p-4">
              <div className="bg-purple-100 rounded-full p-3 w-fit mx-auto mb-3">
                <GraduationCap className="h-6 w-6 text-purple-600" />
              </div>
              <h3 className="font-semibold text-gray-900 mb-2">Class Management</h3>
              <p className="text-sm text-gray-600">Efficient class and subject organization</p>
            </div>
            <div className="p-4">
              <div className="bg-orange-100 rounded-full p-3 w-fit mx-auto mb-3">
                <Shield className="h-6 w-6 text-orange-600" />
              </div>
              <h3 className="font-semibold text-gray-900 mb-2">Secure Access</h3>
              <p className="text-sm text-gray-600">Role-based security and authentication</p>
            </div>
          </div>
        </div>

        {/* Footer */}
        <footer className="mt-20 text-center text-gray-500">
          <p>&copy; 2024 EduManage School Management System. All rights reserved.</p>
        </footer>
      </div>
    </div>
  )
}
