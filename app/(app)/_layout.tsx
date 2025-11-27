/**
 * App Layout
 * Protected routes layout wrapper
 */

import { Redirect, Stack } from 'expo-router';
import { useAuthStore } from '../../src/store/useAuthStore';
import { LoadingSpinner } from '../../src/components/ui/LoadingSpinner';

export default function AppLayout() {
  const { isAuthenticated, isInitialized } = useAuthStore();

  // Show loading while initializing
  if (!isInitialized) {
    return <LoadingSpinner fullScreen message="Loading..." />;
  }

  // Redirect to login if not authenticated
  if (!isAuthenticated) {
    return <Redirect href="/sign-in" />;
  }

  return (
    <Stack screenOptions={{ headerShown: false }}>
      <Stack.Screen name="(tabs)" />
      <Stack.Screen
        name="settings"
        options={{
          presentation: 'modal',
          animation: 'slide_from_bottom',
        }}
      />
      <Stack.Screen name="grades/[subjectId]" />
      <Stack.Screen name="subjects/[id]" />
    </Stack>
  );
}
