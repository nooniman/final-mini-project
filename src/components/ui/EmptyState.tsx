/**
 * EmptyState Component
 * Displays when no data is available
 */

import React from 'react';
import { View, StyleSheet } from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';
import { ThemedText } from './ThemedText';
import { Button } from './Button';

interface EmptyStateProps {
  icon?: keyof typeof Ionicons.glyphMap;
  title: string;
  message?: string;
  actionLabel?: string;
  onAction?: () => void;
}

export function EmptyState({
  icon = 'folder-open-outline',
  title,
  message,
  actionLabel,
  onAction,
}: EmptyStateProps) {
  const { colors } = useTheme();

  return (
    <View style={styles.container}>
      <Ionicons
        name={icon}
        size={64}
        color={colors.textMuted}
        style={styles.icon}
      />
      <ThemedText variant="title3" style={styles.title}>
        {title}
      </ThemedText>
      {message && (
        <ThemedText
          variant="subheadline"
          color="secondary"
          style={styles.message}
          align="center"
        >
          {message}
        </ThemedText>
      )}
      {actionLabel && onAction && (
        <Button
          title={actionLabel}
          onPress={onAction}
          variant="outline"
          size="sm"
          style={styles.button}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: Layout.spacing.xl,
  },
  icon: {
    marginBottom: Layout.spacing.md,
  },
  title: {
    marginBottom: Layout.spacing.sm,
    textAlign: 'center',
  },
  message: {
    marginBottom: Layout.spacing.lg,
    maxWidth: 280,
  },
  button: {
    marginTop: Layout.spacing.sm,
  },
});

export default EmptyState;
