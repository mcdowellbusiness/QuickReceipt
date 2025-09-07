import React from 'react';
import { useAuth } from '../../contexts/AuthContext';
import { useResponsive } from '../../hooks/useResponsive';
import ResponsiveLayout from '../../components/layout/ResponsiveLayout';
import MobileNav from '../../components/mobile/MobileNav';
import AdminSidebar from '../../components/admin/AdminSidebar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../../components/ui/Card';
import { Receipt, Users, DollarSign, TrendingUp } from 'lucide-react';

const DashboardPage: React.FC = () => {
  const { user, isAuthenticated } = useAuth();
  const { isMobile, isDesktop } = useResponsive();

  if (!isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold mb-4">Please log in to continue</h1>
          <p className="text-muted-foreground">You need to be authenticated to access the dashboard.</p>
        </div>
      </div>
    );
  }

  // Mock data - in real app, this would come from API
  const stats = [
    {
      title: 'Total Receipts',
      value: '24',
      change: '+12%',
      changeType: 'positive' as const,
      icon: Receipt,
    },
    {
      title: 'Team Members',
      value: '8',
      change: '+2',
      changeType: 'positive' as const,
      icon: Users,
    },
    {
      title: 'Total Spent',
      value: '$2,450',
      change: '+5.2%',
      changeType: 'positive' as const,
      icon: DollarSign,
    },
    {
      title: 'Budget Used',
      value: '68%',
      change: '-3.1%',
      changeType: 'negative' as const,
      icon: TrendingUp,
    },
  ];

  const recentReceipts = [
    { id: 1, description: 'Office supplies', amount: 45.99, date: '2024-01-15' },
    { id: 2, description: 'Team lunch', amount: 120.50, date: '2024-01-14' },
    { id: 3, description: 'Software license', amount: 299.00, date: '2024-01-13' },
  ];

  const isAdmin = user?.email?.includes('admin'); // Simple admin check

  return (
    <ResponsiveLayout
      showAdminSidebar={isDesktop && isAdmin}
      adminSidebar={<AdminSidebar />}
      mobileHeader={<MobileNav />}
    >
      <div className="p-4 md:p-6 space-y-6">
        {/* Welcome Header */}
        <div>
          <h1 className="text-2xl md:text-3xl font-bold">Welcome back, {user?.name}!</h1>
          <p className="text-muted-foreground">
            Here's what's happening with your receipts and budgets today.
          </p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          {stats.map((stat) => {
            const Icon = stat.icon;
            return (
              <Card key={stat.title}>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">
                        {stat.title}
                      </p>
                      <p className="text-2xl font-bold">{stat.value}</p>
                    </div>
                    <Icon className="h-8 w-8 text-muted-foreground" />
                  </div>
                  <div className="mt-2">
                    <span
                      className={`text-sm font-medium ${
                        stat.changeType === 'positive'
                          ? 'text-green-600'
                          : 'text-red-600'
                      }`}
                    >
                      {stat.change}
                    </span>
                    <span className="text-sm text-muted-foreground ml-1">
                      from last month
                    </span>
                  </div>
                </CardContent>
              </Card>
            );
          })}
        </div>

        {/* Recent Receipts */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Recent Receipts</CardTitle>
              <CardDescription>
                Your latest expense receipts
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {recentReceipts.map((receipt) => (
                  <div
                    key={receipt.id}
                    className="flex items-center justify-between p-3 rounded-lg border"
                  >
                    <div>
                      <p className="font-medium">{receipt.description}</p>
                      <p className="text-sm text-muted-foreground">
                        {new Date(receipt.date).toLocaleDateString()}
                      </p>
                    </div>
                    <p className="font-semibold">${receipt.amount}</p>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Quick Actions */}
          <Card>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
              <CardDescription>
                Common tasks you might want to do
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                <button className="w-full p-3 text-left rounded-lg border hover:bg-accent transition-colors">
                  <div className="font-medium">Upload Receipt</div>
                  <div className="text-sm text-muted-foreground">
                    Add a new expense receipt
                  </div>
                </button>
                <button className="w-full p-3 text-left rounded-lg border hover:bg-accent transition-colors">
                  <div className="font-medium">View Budgets</div>
                  <div className="text-sm text-muted-foreground">
                    Check your spending limits
                  </div>
                </button>
                <button className="w-full p-3 text-left rounded-lg border hover:bg-accent transition-colors">
                  <div className="font-medium">Team Management</div>
                  <div className="text-sm text-muted-foreground">
                    Manage team members and roles
                  </div>
                </button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </ResponsiveLayout>
  );
};

export default DashboardPage;
