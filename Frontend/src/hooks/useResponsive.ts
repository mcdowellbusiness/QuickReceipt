import { useState, useEffect } from 'react';
import type { ScreenSize } from '../types';

interface Breakpoints {
  xs: number;
  sm: number;
  md: number;
  lg: number;
  xl: number;
  '2xl': number;
}

const breakpoints: Breakpoints = {
  xs: 475,
  sm: 640,
  md: 768,
  lg: 1024,
  xl: 1280,
  '2xl': 1536,
};

export function useResponsive() {
  const [screenSize, setScreenSize] = useState<ScreenSize>('xs');
  const [windowWidth, setWindowWidth] = useState<number>(0);

  useEffect(() => {
    const updateScreenSize = () => {
      const width = window.innerWidth;
      setWindowWidth(width);

      if (width >= breakpoints['2xl']) {
        setScreenSize('2xl');
      } else if (width >= breakpoints.xl) {
        setScreenSize('xl');
      } else if (width >= breakpoints.lg) {
        setScreenSize('lg');
      } else if (width >= breakpoints.md) {
        setScreenSize('md');
      } else if (width >= breakpoints.sm) {
        setScreenSize('sm');
      } else {
        setScreenSize('xs');
      }
    };

    // Set initial value
    updateScreenSize();

    // Add event listener
    window.addEventListener('resize', updateScreenSize);

    // Cleanup
    return () => window.removeEventListener('resize', updateScreenSize);
  }, []);

  const isMobile = screenSize === 'xs' || screenSize === 'sm';
  const isTablet = screenSize === 'md';
  const isDesktop = screenSize === 'lg' || screenSize === 'xl' || screenSize === '2xl';
  const isLargeDesktop = screenSize === 'xl' || screenSize === '2xl';

  return {
    screenSize,
    windowWidth,
    isMobile,
    isTablet,
    isDesktop,
    isLargeDesktop,
    breakpoints,
  };
}
