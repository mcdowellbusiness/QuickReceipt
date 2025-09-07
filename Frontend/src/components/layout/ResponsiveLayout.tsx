import React from 'react';
import { useResponsive } from '../../hooks/useResponsive';
import type { BaseComponentProps } from '../../types';
import { cn } from '../../utils/cn';

interface ResponsiveLayoutProps extends BaseComponentProps {
  showAdminSidebar?: boolean;
  adminSidebar?: React.ReactNode;
  mobileHeader?: React.ReactNode;
  mobileFooter?: React.ReactNode;
}

const ResponsiveLayout: React.FC<ResponsiveLayoutProps> = ({
  children,
  className,
  showAdminSidebar = false,
  adminSidebar,
  mobileHeader,
  mobileFooter,
}) => {
  const { isMobile, isDesktop } = useResponsive();

  if (isMobile) {
    return (
      <div className={cn('min-h-screen bg-background', className)}>
        {/* Mobile Header */}
        {mobileHeader && (
          <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            {mobileHeader}
          </header>
        )}

        {/* Mobile Content */}
        <main className="flex-1">
          {children}
        </main>

        {/* Mobile Footer */}
        {mobileFooter && (
          <footer className="border-t bg-background">
            {mobileFooter}
          </footer>
        )}
      </div>
    );
  }

  if (isDesktop) {
    return (
      <div className={cn('min-h-screen bg-background', className)}>
        <div className="flex h-screen">
          {/* Desktop Sidebar - Only show if admin and showAdminSidebar is true */}
          {showAdminSidebar && adminSidebar && (
            <aside className="w-64 border-r bg-card">
              {adminSidebar}
            </aside>
          )}

          {/* Desktop Main Content */}
          <div className="flex-1 flex flex-col overflow-hidden">
            {/* Desktop Header */}
            {mobileHeader && (
              <header className="border-b bg-background">
                {mobileHeader}
              </header>
            )}

            {/* Desktop Content */}
            <main className="flex-1 overflow-auto">
              {children}
            </main>
          </div>
        </div>
      </div>
    );
  }

  // Tablet layout (fallback)
  return (
    <div className={cn('min-h-screen bg-background', className)}>
      <main className="container mx-auto px-4 py-8">
        {children}
      </main>
    </div>
  );
};

export default ResponsiveLayout;
