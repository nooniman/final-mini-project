/**
 * Index Route
 * Redirects to appropriate screen based on auth state
 */

import { Redirect } from 'expo-router';
import { useAuthStore } from '../src/store/useAuthStore';
import { LoadingSpinner } from '../src/components/ui/LoadingSpinner';

export default function Index() {
  const { isAuthenticated, isInitialized } = useAuthStore();

  // Show loading while initializing
  if (!isInitialized) {
    return <LoadingSpinner fullScreen message="Loading..." />;
  }

  // Redirect based on auth state
  if (isAuthenticated) {
    return <Redirect href="/(app)/(tabs)/dashboard" />;
  }

  return <Redirect href="/sign-in" />;
}
