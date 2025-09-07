import React from 'react';
import type { BaseComponentProps } from '../../types';
import { cn } from '../../utils/cn';

interface CardProps extends BaseComponentProps {
  variant?: 'default' | 'outlined' | 'elevated';
}

const Card: React.FC<CardProps> = ({
  children,
  className,
  variant = 'default',
  ...props
}) => {
  const variantClasses = {
    default: 'bg-card text-card-foreground',
    outlined: 'border border-border bg-card text-card-foreground',
    elevated: 'bg-card text-card-foreground shadow-lg',
  };

  return (
    <div
      className={cn(
        'rounded-lg border bg-card text-card-foreground shadow-sm',
        variantClasses[variant],
        className
      )}
      {...props}
    >
      {children}
    </div>
  );
};

const CardHeader: React.FC<BaseComponentProps> = ({
  children,
  className,
  ...props
}) => {
  return (
    <div
      className={cn('flex flex-col space-y-1.5 p-6', className)}
      {...props}
    >
      {children}
    </div>
  );
};

const CardTitle: React.FC<BaseComponentProps> = ({
  children,
  className,
  ...props
}) => {
  return (
    <h3
      className={cn('text-2xl font-semibold leading-none tracking-tight', className)}
      {...props}
    >
      {children}
    </h3>
  );
};

const CardDescription: React.FC<BaseComponentProps> = ({
  children,
  className,
  ...props
}) => {
  return (
    <p
      className={cn('text-sm text-muted-foreground', className)}
      {...props}
    >
      {children}
    </p>
  );
};

const CardContent: React.FC<BaseComponentProps> = ({
  children,
  className,
  ...props
}) => {
  return (
    <div className={cn('p-6 pt-0', className)} {...props}>
      {children}
    </div>
  );
};

const CardFooter: React.FC<BaseComponentProps> = ({
  children,
  className,
  ...props
}) => {
  return (
    <div className={cn('flex items-center p-6 pt-0', className)} {...props}>
      {children}
    </div>
  );
};

export { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter };
